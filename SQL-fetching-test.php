<?php
/* Template Name: Admin page */
// Create connection
//$conn = new mysqli("localhost", "root", "root", "wpdb");

global $wpdb;

// Check connection

if (!$wpdb->check_connection()) {
    echo fuck;
    //die();
}

$sql = "SELECT name FROM wp_items";
$result = $wpdb->get_results($sql);

//var_dump($result);

//echo $result->name;

if ($result[0] != NULL) {
    // output data of each row
    foreach($result as $p){
      echo "Name: " . $p->name . "<br>";
    }
  } else {
      echo "0 results";
  }
    /*while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"] . "<br>";
    }*/

//$conn->close();
?>
