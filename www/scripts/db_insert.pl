#!/usr/bin/perl 

#
# db_insert.pl
#
# Developed by Clayton Dukes <cdukes@cdukes.com>
# Copyright (c) 2009 gdd.net
# Licensed under terms of GNU General Public License.
# All rights reserved.
#
# Changelog:
# 2009-05-28 - created
# 2009-09-11 - added a fork to child process to stop I/O blocking which was causing high CPU when SQZ was enabled
# 2009-09-14 - Changed re_pipe to allow for missing prg fields
#			 - Replaced the incoming date and time with the machine's date and time since not everyone uses NTP
# 2009-10-09 - Added command line parameters to allow better control when testing
#

use strict;
use POSIX qw/strftime/;
use DBI;
use Text::LevenshteinXS qw(distance);
use File::Spec;
use File::Basename;
use Benchmark;

$| = 1;

#
# Declare variables to use
#
use vars qw/ %opt /;

# Set command line vars
my ($debug, $config, $logfile, $verbose, $selftest);

#
# Command line options processing
#
sub init()
{
	use Getopt::Std;
	my $opt_string = 'hd:c:l:sv';
	getopts( "$opt_string", \%opt ) or usage();
	usage() if $opt{h};
	$debug = defined($opt{'d'}) ? $opt{'d'} : '0';
	$logfile = $opt{'l'} if $opt{'l'};
	$verbose = $opt{'v'} if $opt{'v'};
	$selftest = $opt{'s'} if $opt{'s'};
	$config = defined($opt{'c'}) ? $opt{'c'} : "/var/www/html/config/config.php";
}

init();

if ($selftest) {
	my $cmd = "$0";
	$cmd .= " -d 1"; # Force debug on so test results are shown 
	$cmd .= " -c " . $opt{'c'} if $opt{'c'};
	$cmd .= " -l " . $opt{'l'} if $opt{'l'};
	$cmd .= " -v "; # Force verbose mode so results are printed to screen
	my $date = strftime("%Y-%m-%d", localtime);
	my $time = strftime("%H:%M:%S", localtime);
	print STDOUT "\nPERFORMING SELF TEST USING COMMAND:\n$cmd\n\n";
	my $res = `printf "host\tlocal7\terr\terr\ttest\t$date\t$time\tDB_INSERT Test\t12345: %%SYS-5-CONFIG_I: Configured from 172.16.0.123 by Fred Flinstone\n" | $cmd`;
	print STDOUT "$res\n";
	print STDOUT "SELF TEST COMPLETE!\n";
	exit;
}


#
# Help message
#
sub usage()
{
	print STDERR << "EOF";
This program is used to process incoming syslog streams from the syslog-ng 'program' pipe.
If no command line parameters are given, it will simply accept incoming data from the pipe. 
	usage: $0 [-hdvlcs] 
	-h        : this (help) message
	-d        : debug level (0-5) (0 = disabled [default])
	-v        : Also print results to STDOUT
	-l        : log file (default used from config.php if not set here)
	-c        : config file (overrides the default config.php file location set in the '\$config' variable in this script)
	example: $0 -l /var/log/foo.log -d 5 -c /var/www/html/config/config.php -v

	-s        : **Special Option**: 
			This option may be used to run a self test
			You can run a self test by typing:
			$0 -s -c /var/www/html/config/config.php (replace with the path to your config)
EOF
	exit;
}


local $SIG{INT} = 'IGNORE' if not $opt{'d'};

# BELOW is NO longer used, change default above or set from command line.
# my $ngconfig = "/var/www/html/config/config.php";


if (! -f $config) {
	print STDOUT "Can't open config file \"$config\" : $!\nTry $0 -h\n"; 
	exit;
} 
open( CONFIG, $config );
my @config = <CONFIG>; 
close( CONFIG );

my($dbtable,$dbuser,$dbpass,$db,$dbhost,$dbport,$DEBUG,$SQZ,$SQZ_TIME,$SQZ_DIST,$LOG_PATH,$bulk_ins,$insert_string,$SQL_BULK_INS,@msgs,$seq, @bmdata, $bmstart, $bmend);
foreach my $var (@config) {
	next unless $var =~ /^define/; # read only def's
	$dbtable = $1 if ($var =~ /'DEFAULTLOGTABLE', '(\w+)'/);
	$dbuser = $1 if ($var =~ /'DBADMIN', '(\w+)'/);
	$dbpass = $1 if ($var =~ /'DBADMINPW', '(\w+)'/);
	$db = $1 if ($var =~ /'DBNAME', '(\w+)'/);
	$dbhost = $1 if ($var =~ /'DBHOST', '(\w+.*|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})'/);
	$dbport = $1 if ($var =~ /'DBPORT', '(\w+)'/);
	$DEBUG = $1 if ($var =~ /'DEBUG', (\w+)/);
	$SQZ = $1 if ($var =~ /'SQZ_ENABLED', (\w+)/);
	$SQZ_TIME = $1 if ($var =~ /'SQZ_TIME', '(\d+)'/);
	$SQZ_DIST = $1 if ($var =~ /'SQZ_DIST', '(\d+)'/);
	$LOG_PATH = $1 if ($var =~ /'LOG_PATH', '(\/.*)'/);
	$SQL_BULK_INS = $1 if ($var =~ /'SQL_BULK_INS', '(\d+)'/);
}
if (( ! $SQZ_DIST ) || (! $SQL_BULK_INS)){
	print "Error: Unable to read config variables from $config\n";
	exit;
}

# If debug is set in config.php, then increment debug to at least 1
$debug++ if $DEBUG eq "TRUE";


# Initialize some vars for later use
my $insert = 1;
my ($distance,$datetime_now,$datetime_past,$fo);
my (@rows, @fos, @inserts);
my $counter = 1;
my $datetime = strftime("%Y-%m-%d %H:%M:%S", localtime);
my $pidfile = "/var/run/".basename($0, ".pl").".pid";
my $message;
$logfile = "$LOG_PATH/".basename($0, ".pl").".log" if not ($logfile);
my $file_path = File::Spec->rel2abs($0);

mkdir $LOG_PATH;
if (! -d $LOG_PATH) {
	print STDOUT "Failed to create $LOG_PATH: $!\n";
	exit;
}
open(LOG,">>$logfile");
if (! -f $logfile) {
	print STDOUT "Unable to open log file \"$logfile\" for writing...$!\n";
	exit;
}
select(LOG);
print LOG "\n$datetime\nStarting $logfile for $file_path at pid $$\n";
print LOG "Using Database: $db\n";
print STDOUT "\n$datetime\nStarting $logfile for $file_path at pid $$\n" if (($debug > 0) and ($verbose));
print STDOUT "Using Database: $db\n" if (($debug > 0) and ($verbose));

if (($debug gt 0) or ($verbose)) { 
	print STDOUT "Debug level: $debug\n";
	print STDOUT "Table: $dbtable\n";
	print STDOUT "Adminuser: $dbuser\n";
	print STDOUT "PW: $dbpass\n";
	print STDOUT "DB: $db\n";
	print STDOUT "DB Host: $dbhost\n";
	print STDOUT "DB Port: $dbport\n";
	print STDOUT "Squeeze Feature = $SQZ\n";
	print STDOUT "Logging results to $logfile\n";
	print STDOUT "Printing results to screen (STDOUT)\n" if (($debug > 0) and ($verbose));
}

# Set vars and pattern match outside the loop to speed up regex processing
my ($host, $facility, $priority, $level, $tag, $date, $time, $prg, $msg); 
my $re_pipe = qr/(.+?)[\t](.*)[\t](.*)[\t](.*)[\t](.*)[\t](.*)[\t](.*)[\t](.*)[\t](.*)/;

my $pid;
my $dbh;
# open the pipe for reading
while (<>){
	# start benchmark timer 
	$bmstart = new Benchmark;
	if (($pid = fork) == 0) {
		# Setup DB connection inside the loop since we are forking
		$dbh = DBI->connect( "DBI:mysql:$db:$dbhost", $dbuser, $dbpass );
		if (!$dbh) {
			print LOG "Can't connect to $db database: ", $DBI::errstr, "\n" if ($debug gt 0);
			print STDOUT "Can't connect to $db database: ", $DBI::errstr, "\n";
			exit;
		}
		$dbh->{InactiveDestroy} = 1;
		# Prepare database statements for later use
		my $db_select = $dbh->prepare("SELECT * FROM $dbtable WHERE host=? AND facility=? AND priority=? AND level=? AND tag=? AND fo between ? AND ?");
		my $db_select_id = $dbh->prepare("SELECT * FROM $dbtable WHERE id=?");
		my $db_update = $dbh->prepare("UPDATE $dbtable SET counter=?, fo=?, lo=? WHERE id=?");
		my $db_del = $dbh->prepare("DELETE FROM $dbtable WHERE id=?");
		my $db_insert = $dbh->prepare("INSERT INTO $dbtable (host,facility,priority,level,tag,program,msg,fo,lo,seq) VALUES (?,?,?,?,?,?,?,?,?,?)");

		print LOG "\n\nINCOMING MESSAGE:\n$_\n" if ($debug gt 0);
		print STDOUT "\n\nINCOMING MESSAGE:\n$_\n" if (($debug > 0) and ($verbose));

		# Get current date and time
		$datetime_now = strftime("%Y-%m-%d %H:%M:%S", localtime);

		# Get current date and time minus $SQZ_TIME in seconds (5 minutes by default)
		$datetime_past = strftime("%Y-%m-%d %H:%M:%S", localtime(time - $SQZ_TIME));

		# Get incoming variables from PIPE
		if ($_ =~ m/$re_pipe/) {
			$host = $1;
			$facility = $2;
			$priority = $3;
			$level = $4;
			$tag = $5;
			$date = strftime("%Y-%m-%d", localtime); # Changed to use machine's local date and time in case sending device is off
			$time = strftime("%H:%M:%S", localtime);
			$prg = $8;
			$msg = $9;
			$msg =~ s/\\//; # Some messages come in with a trailing slash
			$msg =~ s/'//; # remove any ''s
			$msg =~ s/[\x00-\x1F\x80-\xFF]//; # Remove any non-printable characters
			$prg =~ s/%ACE.*\d+/Cisco ACE/; # Added because ACE modules don't send their program field properly
			$prg =~ s/%ASA.*\d+/Cisco ASA/; # Added because ASA's don't send their program field properly
			$prg =~ s/date=\d+-\d+-\d+/Fortigate Firewall/; # Added because Fortigate's don't follow IETF standards
			$msg =~ s/time=\d+:\d+:\d+\s//; # Added because Fortigate's don't s follow IETF standards
			@msgs = split(/:/, $msg);
			if ($prg =~ /^\d+/) { # Some messages come in with the sequence as the PROGRAM field
				$prg = "Cisco Syslog";
			}
			if (!$prg) {
				$prg = "Syslog";
			}
			# Added below to strip paths from program names so that just the program is listed
			# i.e.: /USR/SBIN/CRON would be inserted into the DB as just CRON
			if ($prg =~ /\//) { 
				$prg = fileparse($prg);
			}
			# Below is an attempt to grab the SEQ id 
			# Note: sequence numbers really are best effort
			# To enable sequence numbers on a cisco device, use "service sequence-numbers"
			# I may remove the SEQ field from the database altogether in future releases.
			if ($msgs[0] =~ /^\d+/) { 
				$seq = $msgs[0];
			}
			$msg =~ s/^$seq\s?:\s?//; # Remove SEQ from the message if it exists
			if ($seq !~ /\d/) {
				$seq = 0;
			}
			if ($debug gt 0) { 
				print LOG "HOST: $host\n";
				print LOG "FAC: $facility\n";
				print LOG "PRI: $priority\n";
				print LOG "LVL: $level\n";
				print LOG "TAG: $tag\n";
				print LOG "DAT: $date\n";
				print LOG "TME: $time\n";
				print LOG "PRG: $prg\n";
				print LOG "SEQ: $seq\n";
				print LOG "MSG: $msg\n\n";
			}
			if (($debug > 0) and ($verbose)) { 
				print STDOUT "HOST: $host\n";
				print STDOUT "FAC: $facility\n";
				print STDOUT "PRI: $priority\n";
				print STDOUT "LVL: $level\n";
				print STDOUT "TAG: $tag\n";
				print STDOUT "DAT: $date\n";
				print STDOUT "TME: $time\n";
				print STDOUT "PRG: $prg\n";
				print STDOUT "SEQ: $seq\n";
				print STDOUT "MSG: $msg\n\n";
			}
		} else {
			# If something gets inserted wrong from the PIPE we'll set host = blank so we can error out later
			$host = "";
			print LOG "INVALID MESSAGE FORMAT:\n$_\n" if ($debug gt 0);
			print STDOUT "INVALID MESSAGE FORMAT:\n$_\n" if (($debug > 0) and ($verbose));
		}
		# If the SQZ feature is enabled, continue, if not we'll just insert the record afterward
		if($SQZ eq "TRUE") {
			$insert = 1;
			# Debug: set trace level to 4 to get query string executed
			# $db_select->{TraceLevel} = 4;
			# Select any records between now and $SQZ_TIME seconds ago that match this host, facility, etc.
			$db_select->execute($host, $facility, $priority, $level, $tag, $datetime_past, $datetime_now);
			if ($db_select->errstr()) {
				print LOG "FATAL: Unable to execute SQL statement: ", $db_select->errstr(), "\n" if ($debug gt 0);
				print STDOUT "FATAL: Unable to execute SQL statement: ", $db_select->errstr(), "\n";
				exit;
			}

			# For each of the rows obtained above, calculate the likeness of messages using a distance measurement
			while (my $ref = $db_select->fetchrow_hashref()) {
				$distance = distance($ref->{'msg'},$msg);

				# If the distance between the two messages is less than $SQZ_DIST then we'll consider it a match and deduplicate the message
				if ($distance < $SQZ_DIST ) {
					# Store the identical record into an array for later processing
					push(@rows, $ref->{'id'});
					print LOG "A duplicate message was found with database id: ".$ref->{'id'}." having a distance of $distance\n" if ($debug gt 0);
					print STDOUT "A duplicate message was found with database id: ".$ref->{'id'}." having a distance of $distance\n" if (($debug gt 0) and ($verbose));
				}
			}
			# If rows matched above, we're now going to process them for deduplication
			my $numrows = scalar @rows;
			if ($numrows > 0) {
				print LOG "Found $numrows duplicate rows\n" if ($debug gt 0);
				print STDOUT "Found $numrows duplicate rows\n" if (($debug > 0) and ($verbose));
				# Next, sort the row id's so that we know the oldest in order to update it later (we only want to update the oldest row and delete the newer ones that are duplicates)
				@rows = sort @rows; 
				# Set the first row as the update row and grab info
				my $update_id = $rows[0];
				$db_select_id->execute($update_id);
				while (my $ref = $db_select_id->fetchrow_hashref()) {
					$fo = $ref->{'fo'};
					# If FO doesn't exist, then set the current datetime instead.
					if (!$fo) { $fo = $datetime_now }
					push (@fos, $fo);
				}
				# Next, for each row found, we're going to select it and get some information such as the fo and counter
				for (my $i=0; $i <= $#rows; $i++) {
					print LOG "Processing rows:\n\tSource: $rows[0]\n\tCurrent: $rows[$i]\n" if ($debug gt 0);
					print STDOUT "Processing rows:\n\tSource: $rows[0]\n\tCurrent: $rows[$i]\n" if (($debug > 0) and ($verbose));
					$db_select_id->execute($rows[$i]);
					while (my $ref = $db_select_id->fetchrow_hashref()) {
						print LOG "Counter from DBID $rows[$i] = ".$ref->{'counter'}."\n" if ($debug gt 0);
						print STDOUT "Counter from DBID $rows[$i] = ".$ref->{'counter'}."\n" if (($debug > 0) and ($verbose));
						$counter = ($counter + $ref->{'counter'});
						print LOG "New Counter = $counter\n" if ($debug gt 0);
						print STDOUT "New Counter = $counter\n" if (($debug > 0) and ($verbose));
					}
					# Sort the arrays so that we get the first ones
					@fos = sort @fos; 
					$fo = $fos[0];
					# if the row returned is greater than 0 (i.e. not the FIRST record) then delete it as a duplicate.
					# Skip the first record (which will be the source ID)
					if ($rows[0] != $rows[$i]) {
						print LOG "DELETING DB Record: $rows[$i] which is a duplicate record of $rows[0]\n" if ($debug gt 0);
						print STDOUT "DELETING DB Record: $rows[$i] which is a duplicate record of $rows[0]\n" if (($debug > 0) and ($verbose));
						$db_del->execute($rows[$i]);
					}
					# Else, if the row returned is the FIRST record, we need to update it with new counter, fo and lo
					print LOG "UPDATING DB Record: $update_id with new counter and timestamps\n" if ($debug gt 0);
					print STDOUT "UPDATING DB Record: $update_id with new counter and timestamps\n" if (($debug > 0) and ($verbose));
					$db_update->execute($counter,$fo,$datetime_now,$update_id);
				}
				# Since we've already done an update of the first record, we don't need to insert anything after this
				$insert = 0;
				# reset vars for new loop
				@rows =();
				@fos = ();
				$counter = 1;
			}
		}
		# Now that the distance test is over we need to insert any new records that either didn't previously exist or because we had the SQZ feature disabled
		if ($insert != 0) {
			if ($host ne "")  {
				print LOG "Starting insert: " . strftime("%H:%M:%S", localtime) ."\n" if ($debug gt 0);
				print STDOUT "Starting insert: " . strftime("%H:%M:%S", localtime) ."\n" if (($debug > 0) and ($verbose));
				$db_insert->execute($host, $facility, $priority, $level, $tag, $prg, $msg, $datetime_now, $datetime_now,$seq);
				if ($db_insert->errstr()) {
					print LOG "FATAL: Can't execute SQL insert statement (", $dbh->errstr(), ")\n" if ($debug gt 0);
					print STDOUT "FATAL: Can't execute SQL insert statement (", $dbh->errstr(), ")\n";
				}
				# $dbh->{TraceLevel} = 4;
				print LOG "Ending insert: " . strftime("%H:%M:%S", localtime) ."\n" if ($debug gt 0);
				print STDOUT "Ending insert: " . strftime("%H:%M:%S", localtime) ."\n" if (($debug > 0) and ($verbose));
			} else {
				print LOG "Error inserting record $_\n" if ($debug gt 0); 
				print STDOUT "Error inserting record $_\n" if (($debug > 0) and ($verbose)); 
			}
		} else {
			print LOG "insert = $insert, Skipping insert of this message since it was a duplicate\n" if ($debug gt 0);
			print STDOUT "insert = $insert, Skipping insert of this message since it was a duplicate\n" if (($debug > 0) and ($verbose));
		}
		$dbh->disconnect();
		exit(1);
	} elsif ($pid > 0) {
		print LOG "Waiting for child on PID $pid to exit...\n" if ($debug > 0);
		print STDOUT "Waiting for child on PID $pid to exit...\n" if (($debug > 0) and ($verbose));
		wait;
	}
	else
	{
		print LOG "Could not fork: errno is $!\n" if ($debug > 0);
		print STDOUT "Could not fork: errno is $!\n" if (($debug > 0) and ($verbose));
	}
	# end benchmark timer 
	$bmend = new Benchmark;
	my $bmdiff = timediff($bmend, $bmstart);
	print LOG "Total processing time was", timestr($bmdiff, 'all'), " seconds\n" if ($debug > 0);
	print STDOUT "Total processing time was", timestr($bmdiff, 'all'), " seconds\n" if (($debug > 0) and ($verbose));
}
close(LOG);
