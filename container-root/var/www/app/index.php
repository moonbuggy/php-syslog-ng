<!DOCTYPE html>
<html>
<head>
<title>Welcome to nginx!</title>
<style>
    body {
        width: 35em;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
    }
</style>
</head>
<body>
<h1>Welcome to nginx!</h1>
<h2>The smebberson/alpine-nginx variant.</h2>
<p>If you see this page, the nginx web server is successfully installed and
working. Further configuration is required.</p>

<h2>The gleb.poljakov/alpine-nginx-php5 variant.</h2>
<?php echo "<p>if you see this text, the PHP is successfully configured and working. Information about PHP on this server is accesseble <a href=\"phpinfo.php\">there</a>. Further configuration is required.</p>" ?>

<h2>Documentation</h2>

<p>For online documentation and support please refer to <a href="http://nginx.org/">nginx.org</a>.<br/>
Commercial support is available at
<a href="http://nginx.com/">nginx.com</a>.</p>

<p>For online documentation specific to the smebberson/alpine-nginx,<br/>
please refer to <a href="https://github.com/smebberson/docker-alpine/alpine-nginx">smebberson/docker-alpine</a>.</p>

<p><em>Thank you for using nginx.</em></p>
</body>
</html>

