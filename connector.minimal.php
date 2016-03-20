<?php

error_reporting(0); // Set E_ALL for debuging

/***** basic auth section *****/
session_start();
$admins = array('admin' => true); // Rewite your setting
if (isset($_GET['login']) || isset($_GET['logout']) || isset($_GET['status'])) {
    $auths = array( // Rewrite your setting
        'admin' => 'yp1223',
        'user1' => 'user1_pass'
    );
    if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_GET['status'])) {
        header("WWW-Authenticate: Basic realm=\"elFinder-demo\"");
        header("HTTP/1.0 401 Unauthorized");
        echo '{"error": "Login faild."}';
        exit;
    } else {
        //  || !isset($_SESSION['ELFINDER_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== $_SESSION['ELFINDER_AUTH_USER']
        if (isset($_GET['logout'])) {
            unset($_SESSION['ELFINDER_AUTH_USER']);
            echo '{"uname": ""}';
        } else {
            if (isset($_SERVER['PHP_AUTH_USER']) && isset($auths[$_SERVER['PHP_AUTH_USER']]) && $auths[$_SERVER['PHP_AUTH_USER']] === $_SERVER['PHP_AUTH_PW']) {
                if (isset($_GET['status'])) {
                    echo '{"uname": "'.(isset($_SESSION['ELFINDER_AUTH_USER'])? $_SESSION['ELFINDER_AUTH_USER'] : '').'"}';
                } else {
                    $_SESSION['ELFINDER_AUTH_USER'] = $_SERVER['PHP_AUTH_USER'];
                    echo '{"uname": "'.$_SERVER['PHP_AUTH_USER'].'"}';
                }
            } else {
                if (isset($_GET['status'])) {
                    echo '{"uname": ""}';
                } else {
                    header("WWW-Authenticate: Basic realm=\"elFinder-demo\"");
                    header("HTTP/1.0 401 Unauthorized");
                    echo '{"error": "Login faild."}';
                    exit;
                }
            }
        }
    }
    exit();
}
$uname = '';
if (!empty($_SESSION['ELFINDER_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER'])) {
    $uname = ($_SESSION['ELFINDER_AUTH_USER'] === $_SERVER['PHP_AUTH_USER'])? $_SERVER['PHP_AUTH_USER'] : '';
}
$isAdmin = isset($admins[$uname]);
$isUser = $uname? true : false;
/******************************/


include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';

/**
 * # Dropbox volume driver need "dropbox-php's Dropbox" and "PHP OAuth extension" or "PEAR's HTTP_OAUTH package"
 * * dropbox-php: http://www.dropbox-php.com/
 * * PHP OAuth extension: http://pecl.php.net/package/oauth
 * * PEAR's HTTP_OAUTH package: http://pear.php.net/package/http_oauth
 *  * HTTP_OAUTH package require HTTP_Request2 and Net_URL2
 */
// Required for Dropbox.com connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDropbox.class.php';

// Dropbox driver need next two settings. You can get at https://www.dropbox.com/developers
// define('ELFINDER_DROPBOX_CONSUMERKEY',    '');
// define('ELFINDER_DROPBOX_CONSUMERSECRET', '');
// define('ELFINDER_DROPBOX_META_CACHE_PATH',''); // optional for `options['metaCachePath']`

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function rwaccess($attr, $path, $data, $volume) {
    return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
        ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
        :  null;                                    // else elFinder decide it itself
}

function roaccess($attr, $path, $data, $volume) {
    return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
        ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
        : ($attr == 'read' || $attr == 'locked');   // else read only
}


// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
    // 'debug' => true,
    'roots' => array(
        array(
            'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
            'path'          => '/var/www/data',                 // path to files (REQUIRED)
            'URL'           => 'http://rainbowstrawberry.dlinkddns.com', // URL to files (REQUIRED)
#            'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
#            'uploadAllow'   => array('image', 'text/plain'),// Mimetype `image` and `text/plain` allowed to upload
#            'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
            'accessControl' => $isAdmin? 'rwaccess' : 'roaccess'  // Admin: R+W, Other: R
        )
    )
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();
