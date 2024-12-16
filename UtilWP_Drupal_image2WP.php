<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Import image from Drupal to file</title>
</head>
<body>
<?php
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
    //$pagename = 'Subjects';
    $pagename = 'Subjects Areas';
    //$pagetype= 'programme_page';  
    $pagetype= 'subject_list_page'; 
    //$pagetype= 'subject_page';   
    //$imagefile="progimages.txt";
    //$imagefile="subjimages.txt";
    $imagefile="subjlistimages.txt";
    //$posttype = "subject";
    $posttype = "subject-area";
    $setfeature = "true";
//
echo "<h2>".$pagename." Images</h2>";
echo '<font size=-1>';
//
// Get pages

$file=fopen($imagefile,"w");
//$sql="SELECT field_data_introduction.*,node.* FROM field_data_introduction, node WHERE field_data_introduction.entity_id = node.nid AND node.type ='programme_page' order by node.title ";
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
	$page_title=$row['title'];
	$nid = $row['nid'];
	echo "<h2>$page_title</h2>";
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
                   // echo($heros[$acount].'<br>');
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
    // set featured image
    if ($setfeature == 'true'){
        // get matching post id
        //$page = new_get_page_by_title( $page_title,OBJECT,'subject' );
        //
        $array_of_objects = get_posts([
            'title' => $page_title,
            'post_type' => 'subject-area',
        ]);
        $page = $array_of_objects[0];//Be sure you have an array with single post or page 
        $post_id = $page->ID;
        echo($post_id);
        //
        // set directory for drupal images
        //$image_file = 'C:\\inetpub\\wwwroot\\unitecWP\\drupal_images\\'.$hero_filename;
        $image_file = 'C:\\inetpub\\wwwroot\\unitecWP\\drupal_images_sublist\\'.$hero_filename;
        echo "<div style='background: #9ae1f5; margin-bottom:1em;'>page id: $post_id</div>";
        echo "<div style='background: #9ae1f5; margin-bottom:1em;'>image: $image_file</div>";
        // attach featured image to post  
        //
        if ( trim($hero_filename) !== ''){
            Generate_Featured_Image( trim($image_file), $post_id );
            $count++;
        }
    } // end if set featured image
} // end while $row
echo('<br>'.$count.' DONE.<br>');
$myconn -> close(); 
fclose($file);
//
//
function Generate_Featured_Image( $image_url, $post_id  ){
    $upload_dir = wp_upload_dir();
    /*
        Array
    (
        [path] => C:\development\xampp\htdocs\example.com/content/uploads/2012/04
        [url] => http://example.com/content/uploads/2012/04
        [subdir] => /2012/04
        [basedir] => C:\~\example.com/content/uploads
        [baseurl] => http://example.com/content/uploads
        [error] => 
    )
    */
    echo($upload_dir['path'].'<br>');
    echo('ImageURL - '.$image_url.'<br>');
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    echo($filename.'<br>');
    $filename = basename($image_url);
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
    echo('<br>put $file - '.$file.'<br>');
    file_put_contents($file, $image_data);
    echo('<br>put feature image'.$filename.'<br>');
    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $post_id, $attach_id );
} 
   
?>



</body>
</html>