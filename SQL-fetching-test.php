<?php
/* Template Name: Admin page */
//Change orderID -> order, and 1/0 -> true/false

global $wpdb;

$sql = "SELECT id FROM wp_orders";
$orders = $wpdb->get_results($sql);

if ($orders[0] != NULL) {
    foreach($orders as $o) {
      echo "<br>Order ID: " . $o->id . "<br>";
      $sql = "SELECT * FROM wp_entries WHERE orderID = " . $o->id;
      $items = $wpdb->get_results($sql);
      foreach ($items as $i) {
          echo "- " . $i->quantity . " x " . $i->item . "<br>";
      }
    }
} else {
  echo "No orders found";
}
?>
