<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Import image from Drupal to file</title>
</head>
<body>
<?php
// create file and set write permission on Users and IIS_IUSRS
//
//die(); // don't run again
echo '<h1>Import image from Drupal to file for feature image</h1>';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting( E_ALL );
// standalone running php
// required include files
require('wp-blog-header.php');
require_once("wp-config.php");
require_once("wp-includes/class-wpdb.php");
/* for wordpress standalone without database use 
// Load WP components, no themes
define('WP_USE_THEMES', false);
require('wp-load.php');
*/
require_once('wp-admin/includes/post.php');
// open drupal data
$db_host        = 'mktprdw1.unitec.ac.nz:3306';  //16/7/18: working. 
$db_username    = 'NewNest';
$db_passwd      = 'unitec11';
$db_name        = 'unitec_dr_prod';
/* MySQL connection to unitec_dr_stage's tables */
$myconn = new mysqli($db_host , $db_username, $db_passwd, $db_name);
if ($myconn->connect_error)
  {
  die("Failed to connect to MySQL: " . $myconn->connect_error);
  }
//settings
    $count=0;
    //$pagename = 'Programmes';
    $pagename = 'Subject Areas';
    //$pagetype= 'programme_page';  
    $pagetype= 'subject_list_page'; 
    //$pagetype= 'subject_page';   
    //$imagefile="progimages.txt";
    $imagefile="subjlistimages.txt";
echo "<h2>".$pagename." Images</h2>";
echo '<font size=-1>';
//
// Get pages
//
$file=fopen($imagefile,"w");
//$sql="SELECT field_data_introduction.*,node.* FROM field_data_introduction, node WHERE field_data_introduction.entity_id = node.nid AND node.type ='programme_page' order by node.title ";
//

//
$sql="SELECT  node.*
       FROM   node
	   WHERE  node.type='".$pagetype."' and node.status=1
	   ORDER BY node.title";
$drupal_prog= $myconn->query($sql);
$count = 0; 
$heros = array();
while ($row = $drupal_prog->fetch_assoc()) {
	//clear variable
	// load fields
	$title=$row['title'];
	$nid = $row['nid'];
	echo "<h2>$title</h2>";
	//
    // get hero image
    // file_managed - fid, filename,uri
    // 
 	$sql9="SELECT field_data_hero_image.*,file_managed.* FROM field_data_hero_image, file_managed WHERE field_data_hero_image.entity_id =".$nid." AND field_data_hero_image.hero_image_fid = file_managed.fid"; 
	$drupal_hero = $myconn->query($sql9);
    $hero_filename = ''; 
	if( $drupal_hero !== false ) {
		//
		while ($row9 = $drupal_hero->fetch_assoc()) {
            $hero_filename=$row9['uri'].PHP_EOL;
            $hero_filename = str_replace('public://','', $hero_filename);
           // echo "<div style='background: #9ae1f5; margin-bottom:1em;'>Hero Filename: $hero_filename</div>";
            // loop through heros array to find duplicates
            $duplicate = 0;
            echo('----'.$hero_filename.'<br>');
            if ($count = 0){
                
            } else {
                echo(count($heros).' count array <br>');
                for ($acount = 0; $acount < count($heros); $acount++) {
                    echo($heros[$acount].'<br>');
                    if ($heros[$acount] == $hero_filename){
                        $duplicate = 1;
                        echo('skip match<br>');
                    }
                } 
            }
            echo($duplicate.'dup array <br>');
            if ($duplicate == 0){
                $heros[] = $hero_filename;
                echo($heros[$count].' -- array <br>');
                if ( trim($hero_filename)!== ''){
                    fwrite($file,$hero_filename);// fwrite()
                }
                $count++;
                echo($count.'<br>');
            } 
		}
	} else {
   		// query2 failed
		echo('<br> sql9 empty<br>\n');
	}
} // end while $row
echo('<br>'.$count.' DONE.<br>');
$myconn -> close(); 
fclose($file);
// 
?>
</body>
</html>