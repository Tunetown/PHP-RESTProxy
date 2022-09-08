<?php 
/**
 * ## What is this?
 * 
 * This is a very simple REST proxy, redirecting all incoming requests exactly via CURL to another URL which is also specified in the request path. 
 * Supports all HTTP methods, paths etc. The incoming request is passed 1:1 to the server including headers and path, then the response from the server is 
 * returned to the caller 1:1.
 * 
 * ## Usage:
 * 
 * The call has to be formatted like follows:
 * 
 * https://scriptlocation.xxx.com/<hostname>/<port>/...
 * 
 * Everything after the port will be passed to the host and port mentioned. An example:
 * 
 * https://scriptlocation.xxx.com/google.com/80/test/foo
 * will call
 * https://google.com:80/test/foo
 * 
 * You have to specify if the proxy uses HTTPS to access the target host. This is defined with the USE_SSL constant and cannot be 
 * changed by the caller.
 * 
 * For path support (REST), in case of apache this goes along with a .htaccess file which redirects all paths to this file. 
 * The .htaccess file contain this to redirect all paths to this index script:
 * 
 * >>>>>>>>>>>> START .htaccess >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
 * 
 * RewriteEngine On
 *
 * RewriteCond %{THE_REQUEST} \ /(.+)\.php
 * RewriteRule ^ /%1 [L,R=301]
 *
 * RewriteCond %{REQUEST_FILENAME} !-l
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} !-d
 * 
 * RewriteRule .* index.php [L,QSA]
 * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
 * 
 * <<<<<<<<<<<< END .htaccess <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 * 
 * If you use another server, please take care yourself.
 * 
 * 
 * (C) Thomas Weber 2021 tom-vibrant@gmx.de
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

const USE_SSL = true;

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * REST Proxy implementation.
 */
class RESTProxy {
    
    /**
     * Use https for accessing the host.
     * 
     * @var boolean
     */    
    private $useSsl = true;
    
    public function __construct($useHttps = true) {
        $this->useSsl = $useHttps;
    }
    
    /**
     * Process the request.
     */
    public function processRequest() {
        // Get path from request and apply the skip levels mechanism to it
        $path = $this->preparePath();

        // Call the CouchDB instance
        $this->callTargetHost($path);
    }
    
    /**
     * Prepare subpath. This can remove an amount of levels from the relative path of the call, see $skipLevels.
     */
    private function preparePath() {
        // Remove the script location from the URI
        $uri = substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['SCRIPT_NAME'])) + 1);
       
        // Split path
        $spl = array_filter(explode('/', $uri));
        
        // Use two levels as database name and port
        $host = array_shift($spl);
        $port = array_shift($spl);
        
        // Compose target URL
        return 'http'.($this->useSsl ? 's' : '').'://'.$host.':'.$port.'/'.implode('/', $spl);
    }
    
    /**
     * Call the remote server and output the response. This is the actual proxy implementation.
     */
    private function callTargetHost($url) {
        // Get incoming body
        $bodySrc = file_get_contents("php://input");
        
        // Get incoming headers
        $headersSrc = array();
        foreach(getallheaders() as $key => $value) {
            array_push($headersSrc, $key.': '.$value);
        }
        
        // Cunstruct the CURL request from this data
        $options = array(
            CURLOPT_CUSTOMREQUEST  => $_SERVER['REQUEST_METHOD'],
            CURLOPT_POSTFIELDS     => $bodySrc,
            CURLOPT_HTTPHEADER     => $headersSrc,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_SSL_VERIFYPEER => 1
        );
        
        // Do the call and get the response (including headers)
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close( $ch );
        
        if (!is_string($response) || !strlen($response)) {
            die("Failure Contacting Server");
        }
        
        // Separate received headers and body
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        
        // Convert received headers to array of lines and set them
        $headerArray = explode("\n", $header);
        foreach($headerArray as $key => $value) {
            if (!$value) continue;
            header($value);
        }
        
        // Return the received body
        echo $body;
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Process the request 
(new RESTProxy(USE_SSL))->processRequest();  

?>