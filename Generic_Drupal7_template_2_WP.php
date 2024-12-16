<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Generic Drupal 7 template Import or Update to WP post type</title>
</head>
<body>
<?php
// This import assumes you are using the WordPress plugin ACF for custom fields.
//
echo '<h1>Generic Drupal 7 template Import or Update to WP post type</h1>';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting( E_ALL );
// standalone running php
// required include files
require('wp-blog-header.php');
require_once("wp-config.php");
require_once("wp-includes/class-wpdb.php");
require_once('wp-admin/includes/post.php');

// open drupal data
$db_host        = 'yourhost';  
$db_username    = 'youruser';
$db_passwd      = 'yourpass';
$db_name        = 'yourdatabase';
/* MySQL connection to tables */
$myconn = new mysqli($db_host , $db_username, $db_passwd, $db_name);
if ($myconn->connect_error)
  {
  die("Failed to connect to MySQL: " . $myconn->connect_error);
  }
// Get template pages
// set template name
$drupal_template = 'yourtemplate';
$WP_post_type = 'yourposttype';
//
echo '<h2>'.$drupal_template.'</h2>';
echo '<font size=-1>';
$count=0;
// drupal node query
$sql="SELECT node.title AS title, node.nid AS id FROM node WHERE node.type = '".$drupal_template."' AND node.title != '' AND node.status = 1 ORDER BY title;";
$drupal= $myconn->query($sql);
// loop through nodes
while ($row = $drupal->fetch_assoc()) {
	//clear variable
	$content_value = '';
	$excerpt_value = '';
	// load fields
	$page_status=$row['status'];
	$title=$row['title'];
	$nid = $row['nid'];
	$count++;
	echo "<h2>$title</h2>";
	//
	// get  your custom field
	$sqla="SELECT  field_data_yourcustomfield.* 
        FROM   field_data_yourcustomfield
	    WHERE  field_data_yourcustomfield.entity_id='$nid' AND field_data_yourcustomfield.bundle = '".$drupal_template."'";
	//echo($sqla);
	$yourcustomfield ='';
	$drupal_field = $myconn->query($sqla); 
	if( $drupal_field !== false ) { 
		while ($rowa = $drupal_field->fetch_assoc()) {
		  $drupal_field=$rowa['yourcustomfield_value'];
		  echo "<div style='background: #f59c9a; margin-bottom:1em;'>yourcustomfield: $yourcustomfield</div>";
		}
	} else {
   // query a failed
		echo('<br> sqla empty<br>\n');
	}
	// get Introduction -  - field_data_introduction, introduction_value
	$sqlb="SELECT  field_data_introduction.* 
        FROM   field_data_introduction
	    WHERE  field_data_introduction.entity_id='$nid'";
	//echo($sqlb);
    $intro='';
	$drupal_intro = $myconn->query($sqlb); 
	if( $drupal_intro !== false ) { 
		while ($rowb = $drupal_intro->fetch_assoc()) {
		  $intro=$rowb['introduction_value'];
		  echo "<div style='background: #D5D5D5; margin-bottom:1em;'>Introduction:".$intro." </div>";
		}
	} else {
   // query a failed
		echo('<br> sqlb empty<br>\n');
	}
	// get the content
	$sqlc="SELECT field_data_body.* FROM field_data_body WHERE  field_data_body.entity_id=".$nid;
	$drupal_body = $myconn->query($sqlc);
	if( $drupal_body !== false ) {
		//
		while ($rowc = $drupal_body->fetch_assoc()) {
		  $content_value=$rowc['body_value'];
		  echo "<div style='background: #D5D5D5; margin-bottom:1em;'>Content: $content_value</div>";
		}
	} else {
   		// query2 failed
		echo('<br> sqlc empty<br>\n');
	}
	// get the excerpt
	$sqld="SELECT field_data_excerpt.* FROM field_data_excerpt WHERE  field_data_excerpt.entity_id=".$nid;
	$drupal_excerpt = $myconn->query($sqld);
	if( $drupal_excerpt !== false ) {
		//
		while ($rowd = $drupal_excerpt->fetch_assoc()) {
		  $excerpt_value=$rowd['excerpt_value'];
		  echo "<div style='background: #D5D5D5; margin-bottom:1em;'>Excerpt: $excerpt_value</div>";
		}
	} else {
   		// query2 failed
		echo('<br> sqld empty<br>\n');
	}
// insert into wordpress
// Gather post data.	
$insert_check = 'false'; // set to true to add/update posts 
    
if ($insert_check == 'true'){
    // check if post type exists, then update, otherwise add
    $found_post = post_exists( $title,'','',$WP_post_type);
    if ($found_post){
        // update existing post
            $the_post = array(
              'ID'           => $found_post,
              'post_content'  => $content_value,
              'post_excerpt' => $excerpt_value,
            );
            wp_update_post( $the_post );
            echo "<br>Post updated successfully. Post ID: " . $found_post;
            // update custom fields
            echo "<div style='background: #D5D5D5; margin-bottom:1em;'>Updating ACFs</div>";
            // update ACF custom field(s)
            //
            // your_acf_field
            if (isset($yourcustomfield )) {
                if(metadata_exists('post', $found_post, 'your_acf_field')) {
                    update_post_meta( $post_id, 'your_acf_field', $yourcustomfield );
                } else {
                    // add field if it doesn't exist
                    add_post_meta( $post_id, 'your_acf_field', $yourcustomfield );
                    // set the ACF field registration number for field_#########
                    add_post_meta( $post_id, '_$your_acf_field', 'field_########' );  
                }
            } 
            // An Intro
            if (isset($intro)) {
                if(metadata_exists('post', $found_post, 'An_Intro')) {
                    update_post_meta( $post_id, 'An_Intro', $intro );
                } else {
                    // add field if it doesn't exist
                    add_post_meta( $post_id, 'An_Intro', $intro );
                    // set the ACF field registration number for field_#########
                    add_post_meta( $post_id, '_An_Intro', 'field_#########' );
                }
            }
            echo('<br>WP fields update - DONE.<br>');
        //end wp update
    } else {
        $my_post = array(
            'post_title'    => $title,
            'post_content'  => $content_value,
            'post_status'   => 'publish',
            'post_author'   => 1, 
            'post_excerpt'   => $excerpt_value,
            'post_type' => $WP_post_type,
            'post_date' => date( 'Y-m-d H:i:s', time() )
            //'page_template'   => 'page-template.php'
        );
        // Insert the post into the database.
        $post_id = wp_insert_post($my_post);
        // Check if there was an error during post insertion
        if (is_wp_error($post_id)) {
            // Error occurred while inserting the post
            echo "Error: " . $post_id->get_error_message();
        } else {
            // The post was successfully inserted, and $post_id contains the post ID
            echo "<br>Post inserted successfully. New Post ID: " . $post_id;
        }
        // insert custom fields
        echo "<div style='background: #D5D5D5; margin-bottom:1em;'>Adding ACFs</div>";
        // Add ACF custom field(s)
        //
        // your_acf_field
        if (isset($yourcustomfield )) {
            add_post_meta( $post_id, 'your_acf_field', $yourcustomfield );
            // set the ACF field registration number for field_#########
            add_post_meta( $post_id, '_$your_acf_field', 'field_########' );
        } 
        // An Intro
        if (isset($intro)) {
            add_post_meta( $post_id, 'An_Intro', $intro );
            // set the ACF field registration number for field_#########
            add_post_meta( $post_id, '_An_Intro', 'field_#########' );
        }
        //end wp insert
        echo('<br>WP insert - DONE.<br>');
        } // endif $foundpost
} // endif $insert_check
//

    
} // end while $row
echo('<br>'.$count.'All DONE.<br>');
$myconn -> close();
//
?>
</body>
</html>