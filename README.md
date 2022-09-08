# RESTProxy

(C) T. Weber 2021-2022

## What is this?
This is a very simple REST proxy, redirecting all incoming requests exactly via CURL to another URL which is also specified in the request path. Supports all HTTP methods, paths etc. The incoming request is passed 1:1 to the server including headers and path, then the response from the server is returned to the caller 1:1.

## Usage:
The call has to be formatted like follows:

	https://scriptlocation.xxx.com/<hostname>/<port>/...

Everything after the port will be passed to the host and port mentioned. An example:
 
	https://scriptlocation.xxx.com/google.com/80/test/foo

will call

	https://google.com:80/test/foo
 
You have to specify if the proxy uses HTTPS to access the target host. This is defined with the USE_SSL constant and cannot be 
changed by the caller.

## Installation
Just put the index file on your server, which must provide PHP >= 7.3 and CURL installed.

For path support (REST), you also need a .htaccess file which redirects all paths to this file. Put this content in the .htaccess file:

	# Enable rewrite engine and route requests to framework
	RewriteEngine On
	
	RewriteCond %{THE_REQUEST} \ /(.+)\.php
	RewriteRule ^ /%1 [L,R=301]
	
	RewriteCond %{REQUEST_FILENAME} !-l
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	
	RewriteRule .* index.php [L,QSA]
	RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
	
	SSLRequireSSL

## License:

This is licensed under the Gnu Public License (GPL) v3 or later.    