/***************************************************
/***************************************************
/ This script will query the joomla database, save 
/ the results of the query to a folder on the server
/ then email the file to a designated email address
/***************************************************
/**************************************************/

<?php

//display errors
error_reporting(E_ALL);
ini_set("display_errors", 1);

//init Joomla Framework 
define('_JEXEC', 1); 
define('JPATH_BASE', dirname(__FILE__).'/');
define('JPATH_COMPONENT',dirname(__FILE__).'/');
define('DS', DIRECTORY_SEPARATOR); 

//echo JPATH_BASE;
require_once (JPATH_BASE .DS. 'includes' .DS. 'defines.php'); 
require_once (JPATH_BASE .DS. 'includes' .DS. 'framework.php');

// Start database stuff
$db = JFactory::getDbo();

//Select records that were created today
$query = "SELECT product_sku FROM #__virtuemart_products WHERE created_on <= NOW();";

$db->setQuery($query);

$result = $db->loadObjectList();
$result = $db->execute();
$my_count = $db->getNumRows($result); 
//echo($my_count);

//Check if any records are returned, if none then end
if ($my_count == 0){
echo "No records found";
exit;
}

if (!$result) die('Couldn\'t fetch records');
$headers = $result -> fetch_fields();
foreach($headers as $header) {
$head[] = $header->name;
}


// Check if the file already exists, if so delete it

$filename = '/home/ladyloving/public_html/temp_cron/new.csv';

if (file_exists($filename)) {
    array_map('unlink', glob($filename));
    //echo "The file $filename has been deleted";
}

$fp = fopen($filename, 'x');
if ($fp && $result) {
fputcsv($fp, array_values($head)); 
while ($row = $result->fetch_array(MYSQLI_NUM)) {
    fputcsv($fp, array_values($row));
}

}    


/********************************************
/
/Send new product sku's to admin
/
/********************************************/

$mailer = JFactory::getMailer();

$config = JFactory::getConfig();
$sender = array( 
    $config->get( 'mailfrom' ),
    $config->get( 'fromname' ) 
);
 
$mailer->setSender($sender);
//echo "it works";
$user = JFactory::getUser();
$recipient = 'D_L_Torr@hotmail.co.uk';

$mailer->addRecipient($recipient);

$body   = "New products have been added to the catalog.";
$mailer->setSubject('New Products Added');
$mailer->setBody($body);
// Optional file attached
$mailer->addAttachment($filename);

$send = $mailer->Send();

if ( $send !== true ) {
    echo 'Error sending email: ' . $send->__toString();
} else {
    echo 'Mail sent';
    }
die;
?>
