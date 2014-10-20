<?php

/**
 * Short url sevice in one file
 *
 * @TODO Add ability to save to CSV file, save time and more data
 * @TODO make so have vanity path and save to separate file that way
 */
include_once '../lib/utils.php';
include_once '../lib/file.php';

$linksfile = 'db/links.ini';
$action    = 'get';
$links     = false;

// base 35 is easier to type
$ourchars = '123456789abcdefghijklmnopqrstuvwxyz';

// ANOTHER WAY TO SALT, but get long ass string
//$str = 'http://rejon.org/';
// changed to 1-9 because we doing base 35 (aka, no 0)
// $str = preg_replace("/[^A-Za-z1-9 ]/", '', substr($str, 0, 6) );
// intval($str);

$ctfile = 'db/counter.txt';



if ( isset($_REQUEST['_id']) && 
        preg_match( "[a-z0-9A-Z]+", $_REQUEST['_id'] ) )
{
    $linksfile = 'db/' . $_REQUEST['_id'];
}


$links = parse_ini_file($linksfile);
if ( FALSE == $links )
    error_log("Could not parse the ini file: $linksfile");
/*
echo "<pre>";
echo '$_REQUEST' . "\n";
var_dump($links);
var_dump($_REQUEST);
echo '$_SERVER' . "\n";
var_dump($_SERVER);
echo "</pre>";
*/

if ( $_REQUEST['a'] );
{
    if ( '/' == substr( $_REQUEST['a'],-1 ) )
        $action = substr( $_REQUEST['a'],0,-1 );
    else
        $action =  $_REQUEST['a'];
}
// echo "ACTION: $action\n";


$lnk = '';
// @TODO removed is_url check for now
// @TODO add back is_url_real which is working, but not really when disconnected
if ( isset($_REQUEST['l']) /* && is_url_real($_REQUEST['l']) */ )
{
    $lnk = $_REQUEST['l'];
}

// check if the url really exists


/* 
 FOR LATER, add way to add vanity url like http://something.com/u2s/agreement/43c2e23cv

// also use this to store to another file.

$mirror = '';
if ( isset($_REQUEST['m']) && is_url( $_REQUEST['m'] ) )
    $mirror = $_REQUEST['m'];
*/


switch ( $action )
{
    case 'set':
        // echo "<p>IS URL: $lnk</p>\n"; 
        // make sure value does not exist
        if ( empty($lnk) )
        {
            // header('HTTP/1.0 404 Not Found');
            $errmsg = 'ERROR: Not a true link.' . "\n";
            // echo $errmsg;
            error_log($errmsg);
            exit;
        }
        $found_key = FALSE;
        if ( !empty($links) )
            $found_key = array_search($lnk, $links); 
        // var_dump($found_key);
        if ( FALSE != $found_key )
        {
            $realpath = get_short_url_path($found_key);
            header("Access-Control-Allow-Origin: *");
            echo "$realpath";
        } else {
            // echo "NOT FOUND KEY";
            // var_dump($found_key);

            $counter = get_counter($ctfile);
            $now = time();
            $oursalt = $counter . $now; 
            $ourcode = base_encode($oursalt, 35, $ourchars);

            $links[$ourcode] = $lnk;
            $status = write_php_ini( $links, $linksfile);
            $shorturl = get_short_url_path($ourcode);
            if ( $status )
            {
                header("Access-Control-Allow-Origin: *");
                echo "$shorturl";
            } else {
                $errmsg = "ERROR: Could not save your short url.\n";
                // echo $errmsg;
                error_log($errmsg);
            }
        }
        exit;
        break;
    case 'get':
    default:
        if ( isset($links[$action]))
        {
            header('Location: ' . $links[$action]);
            exit;
        }
}
header('HTTP/1.0 404 Not Found');
echo 'Unknown link.';
exit;


?>
