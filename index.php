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

$redirects_on = true;
// $redirects_on = false;

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

// dumper($links);
// dumper($_REQUEST);
// dumper($_SERVER);

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
            header('HTTP/1.0 404 Not Found');
            $errmsg = 'ERROR: Not a true link.' . "\n";
            error_log($errmsg);
            exit;
        }
        $found_key = FALSE;
        if ( !empty($links) )
            $found_key = array_search($lnk, $links); 

        if ( FALSE != $found_key )
        {
            $realpath = get_short_url_path($found_key);
            header("Access-Control-Allow-Origin: *");
            echo "$realpath";
        } else {
            $counter = get_counter($ctfile);
            $now = time();
            $oursalt = $counter . $now; 
            $ourcode = base_encode($oursalt, 35, $ourchars);

            // dumper($lnk);

            $links[$ourcode] = $lnk;
            $status = write_php_ini( $links, $linksfile);
            $shorturl = get_short_url_path($ourcode);
            if ( $status )
            {
                header("Access-Control-Allow-Origin: *");
                echo "$shorturl";
            } else {
                $errmsg = "ERROR: Could not save your short url.\n";
                error_log($errmsg);
            }
        }
        exit;
        break;
    case 'get':
    default:
        if ( isset($links[$action]))
        {
            $full_link = '';
            // if you have query string on top of a shortened url
            // then let's just append that to the found string, useful
            // if you need to nest these shorturls
            if ( count($_REQUEST) > 1 )
            {
                $more_args = $_REQUEST;
                unset($more_args['a']);
                // dumper($more_args);
                $tmp_query_string = http_build_query( $more_args );     
                $pos = strpos( $links[$action], '?' );
                $len = strlen( $links[$action] );
                if ( false === $pos )
                    $full_link = $links[$action] . '?' . $tmp_query_string;
                else
                    $full_link = $links[$action] . '&' . $tmp_query_string;
            } else {
                $full_link = $links[$action];
            }

            if ( $redirects_on )
                header('Location: ' . $full_link);
            else
                echo 'Redirects Off: ' . $full_link;
            exit;
        }
}
header('HTTP/1.0 404 Not Found');
echo 'Unknown link.';
exit;


?>
