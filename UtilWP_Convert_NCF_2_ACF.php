<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>WP Native Custom Field to Advanced Custom Field converter</title>
</head>
<body>
<?php
/* 
	First install the Advanced Custom Field (ACF) from https://en-nz.wordpress.org/plugins/advanced-custom-fields/	
	In the installed ACF in wordpress settings, duplicate the existing native custom fields on your site.
	From wp_postmeta table you will need the meta_key value of the NCF.
	From the ACF dashboard setup you will need the field key, which is an alpha-numeric value prefixed with 'field_'.
*/
echo '<h1>WP Native Custom Field (NCF) to Advanced Custom Field (ACF) converter/h1>';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting( E_ALL );
$db_host        = 'localhost';  
$db_username    = 'your-username';
$db_passwd      = 'your-password';
$db_name        = 'your-wordpress-database-name';
/* MySQL connection */
$myconn = new mysqli($db_host , $db_username, $db_passwd, $db_name);
if ($myconn->connect_error)
  {
  die("Failed to connect to MySQL: " . $myconn->connect_error);
  }
echo "<h2>List NCF conversions</h2>\n";	
// set the NCF key	
$meta_key = "old-custom-field";
// Set the new ACF keys	
$field_key = "field_some-alpha-numeric-value";
$extra_meta_key = "_new-custom-field"; // the second ACF record prefixes the meta_key with an underscore '_same-value'
$new_meta_key = "new-custom-field"; // this can be the same name as the old NCF
// set the main query
$sql="SELECT * FROM wp_postmeta WHERE `meta_key` = '".$meta_key."'";
// Get NCF records for all posts that equal the NCF meta_key
$NCF=$myconn->query($sql);
while ($row = $NCF->fetch_assoc()) {
	$id=$row['post_id'];
	$meta_id=$row['meta_id'];
	$meta_value=$row['meta_value'];	
	echo "<h2>$id</h2><br>\n"; 			// post id
	echo "<p>$meta_value</p><br>\n"; 	// custom field value
	echo "<p>$meta_id</p><br>\n"; 		// custom field id
	// Convert existing NCF record to ACF value
	$sql1="UPDATE wp_postmeta SET meta_key = '".$new_meta_key."' WHERE wp_postmeta.meta_id = ".$meta_id;	
	if ($myconn->query($sql1) === TRUE) {
	  echo "NCF record updated to ACF<br>\n";
	} else {
	  echo "Error updating record: <br>\n" . $myconn->error;
	}
	// Insert a new corresponding ACF record for this NCF (ACF uses two records to track its custom fields)
	$sql2="INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, '".$id."', '".$extra_meta_key."', '".$field_key."')";
	if ($myconn->query($sql2) === TRUE) {
	  echo "New ACF record created <br>\n";
	} else {
	  echo "Error Inserting record: <br>\n" . $sql . "<br>" . $myconn->error;
	}
}
$myconn -> close();
?>

</body>
</html>