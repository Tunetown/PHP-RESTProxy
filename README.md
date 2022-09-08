# RESTProxy

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

## Requirements
- Apache web server
- PHP >= 7.3 with CURL module installed.

## Options
The only option is to use HTTPS or HTTP to call the target host. Everything else is controlled by the called URI, see above.

## Installation
Just put the index.php and .htaccess files on your server into some directory.

## License:
(C) Thomas Weber 2021-2022
This is licensed under the Gnu Public License (GPL) v3 or later.    
