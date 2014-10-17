<?php

include_once '../lib/utils.php';
include_once '../lib/file.php';

$linksfile = 'db/links.ini';
$action    = 'get';
$links     = false;

if ( isset($_REQUEST['_id']) && 
        preg_match( "[a-z0-9A-Z]+", $_REQUEST['_id'] ) )
{
    $linksfile = 'db/' . $_REQUEST['_id'];
}


$links = parse_ini_file($linksfile);
if ( FALSE == $links )
    error_log("Could not parse the ini file: $linksfile");

echo "<pre>";
echo '$_REQUEST' . "\n";
var_dump($_REQUEST);
// echo '$_SERVER' . "\n";
// var_dump($_SERVER);
echo "</pre>";

if ( $_REQUEST['a'] );
{
    if ( '/' == substr( $_REQUEST['a'],-1 ) )
        $action = substr( $_REQUEST['a'],0,-1 );
    else
        $action =  $_REQUEST['a'];
}
echo "ACTION: $action\n";


$lnk = '';

switch ( $action )
{
    case 'set':
        if ( isset($_REQUEST['l']) && is_url( $_REQUEST['l'] ) )
            $lnk = $_REQUEST['l'];
        echo "<p>IS URL: $lnk</p>\n"; 
        if ( empty($links) )
        {
            $links = array('a' => $lnk);
            var_dump($links);
        } else {
            // make sure value does not exist
           $found_key = array_search($lnk, $links); 
           if ( FALSE != $found_key )
               echo $found_key;
           else {
               $status = write_php_ini( $links, $linksfile);
               if ( $status )
                   echo "<p>WROTE to $linksfile</p>\n";
               else
                   echo "<p>WROTE to $linksfile</p>\n";
           }
            
        }
        // if not, then save it
        exit;
    case 'get':
        if( isset($_REQUEST['l']) && array_key_exists($_REQUEST['l'], $links) )
        {
            // header('Location: ' . $links[$_GET['l']]);
            echo $_REQUEST['l']; 
            exit;
        }
}
header('HTTP/1.0 404 Not Found');
echo 'Unknown link.';
exit;


exit;


if(isset($_GET['l']) && array_key_exists($_GET['l'], $links)){
        header('Location: ' . $links[$_GET['l']]);
}
else{
        header('HTTP/1.0 404 Not Found');
            echo 'Unknown link.';
}
?>
