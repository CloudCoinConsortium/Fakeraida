** RAIDA **

Dummy RAIDA implementation


In order to use the software you first need to configure VirtualHosts, so as to point multiple DNS names to the server.

Example of APACHE VirtualHost configuration:

<pre>
ServerName cloudcoin.co
ServerAlias www.cloudcoin.co

ServerAlias raida0.srv.cloudcoin.digital
ServerAlias raida1.srv.cloudcoin.digital
ServerAlias raida2.srv.cloudcoin.digital
ServerAlias raida3.srv.cloudcoin.digital
ServerAlias raida4.srv.cloudcoin.digital
ServerAlias raida5.srv.cloudcoin.digital
ServerAlias raida6.srv.cloudcoin.digital
ServerAlias raida7.srv.cloudcoin.digital
ServerAlias raida8.srv.cloudcoin.digital
ServerAlias raida9.srv.cloudcoin.digital
ServerAlias raida10.srv.cloudcoin.digital
ServerAlias raida11.srv.cloudcoin.digital
ServerAlias raida12.srv.cloudcoin.digital
ServerAlias raida13.srv.cloudcoin.digital
ServerAlias raida14.srv.cloudcoin.digital
ServerAlias raida15.srv.cloudcoin.digital
ServerAlias raida16.srv.cloudcoin.digital
ServerAlias raida17.srv.cloudcoin.digital
ServerAlias raida18.srv.cloudcoin.digital
ServerAlias raida19.srv.cloudcoin.digital
ServerAlias raida20.srv.cloudcoin.digital
ServerAlias raida21.srv.cloudcoin.digital
ServerAlias raida22.srv.cloudcoin.digital
ServerAlias raida23.srv.cloudcoin.digital
ServerAlias raida24.srv.cloudcoin.digital


ErrorDocument 400 /error.php
ErrorDocument 401 /error.php
ErrorDocument 402 /error.php
ErrorDocument 403 /error.php
ErrorDocument 404 /error.php
ErrorDocument 405 /error.php
ErrorDocument 408 /error.php
ErrorDocument 500 /error.php
ErrorDocument 503 /error.php

&lt;Directory /path/to/docroot&gt;
        RewriteEngine on

	Allow from all
	AllowOverride None

	RewriteCond %{REQUEST_URI} ^/service/([^/]+)$
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule ^(.*)$ /index.php?service=%1 [L,QSA]
&lt;/Directory&gt;
</pre>

You will need to localy change the resolving process of the domain cloudcoin.co in your 'hosts' file.

Afterwards, the server list can be downloaded from the following URL

<pre>
https://www.cloudcoin.co/servers.html
</pre>

RAIDA behavior is determined by a config file located at the service https://conf.srv.cloudcoin.digital/service/config

Config is in JSON format and has hierarhical architecture. The keyword "inherit" means that the value will be inherited from a parent node.

"mdcoins" key defines an array of results for *multidetect* service. If one of the values is "inherit" than the result for the coin will be retrieved from "detectResult" key.

For instance,

<pre>
"detectResult" : "fail",
"mdcoins" : ["pass", "pass", "fail", "inherit", "pass"]
</pre>

If seven coins are being detected, they will receive these results:

<pre>
pass, pass, fail, fail, pass, fail, fail
</pre>












