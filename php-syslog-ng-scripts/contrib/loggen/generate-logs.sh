#!/bin/sh
# The following command will generate:
# 50 messages per second
# using TCP stream
# for one minute (60 seconds)
# on port 514 

#######################################
# Prompt for a value
#######################################
f_ANSWER()
{
   	printf "%s " "$1"
   	if [ "$2" != "" ] ; then
	   	printf "[%s] " "$2"
   	fi 
	if [ "${DEFAULT:-0}" -eq 0 ] ; then
	   	read ANSWER
   	else
	   	printf "%s\n" "$2"
   	fi
   	if [ "$ANSWER" = "" ] ; then
	   	ANSWER="$2"
   	fi
}

f_ANSWER "Messages per second?" "10"
MPS=$ANSWER
echo "Will run @ $MPS messages per second"
f_ANSWER "Destination Host?" "localhost"
HOST=$ANSWER
f_ANSWER "Port?" "514"
PORT=$ANSWER
f_ANSWER "How Long? (seconds)" "10"
TIME=$ANSWER

echo "Starting loggen"
echo "MPS: $MPS"
echo "TIME: $TIME"
echo "HOST: $HOST"
echo "PORT: $PORT"
echo "Running Command:"
echo "./loggen -r $MPS -D --interval $TIME $HOST $PORT"
./loggen -r $MPS -D --interval $TIME $HOST $PORT
echo "Run completed!"
exit
