<?php
/* Template Name: Admin page */
global $wpdb;
?>

<!-- import bootstrap css -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<!-- HTML TEMPLATE FOR ADMIN SITE -->

<!-- Top navigation bar -->
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">Adminsidan</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="active"><a href="#">Ordrar</a></li>
      <li><a href="#">Ändra toppings</a></li>
      <li><a href="#">Historik</a></li>
    </ul>
  </div>
</nav>

<?php
$sql = "SELECT id FROM wp_orders";
$orders = $wpdb->get_results($sql);

if ($orders[0] != NULL) {
  foreach($orders as $o) {
    echo '<!-- One order block, generate one per order -->
    <div class="container-fluid col-sm-12 col-md-6 col-lg-4">
      <ul class="list-group">
        <!-- top section -->
        <li class="list-group-item" style="min-height: 52px">
          <div>
            <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 22px">
              <b>Order ID: </b> ' . $o->id . '
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
              <div class="btn-group pull-right" style="width:110px">
                <!-- on click: set active order to 0 (so it remains in history) -->
                <button class="btn-sm btn-success">Klar</button>
                <!-- on click: delete order -->
                <button class="btn-sm btn-danger">Radera</button>
              </div>
            </div>
          </div>
        </li>
        <!-- main items section -->
        <li class="list-group-item" style="padding-bottom:0">
          <tstyle style="font-size: 16px">
            Pizzakit
          </tstyle>
          <ul class="list-group" style="padding-top: 5px">
            <!-- generate a <li> for each main item -->';
            $sql = "SELECT * FROM wp_entries, wp_items WHERE wp_entries.item = wp_items.name AND wp_entries.orderID = " . $o->id;
            $items = $wpdb->get_results($sql);
            if ($items[0] != NULL) {
              foreach($items as $i) {
                if ($i->main_item == TRUE) {
                  echo '<li class="list-group-item"><b>' . $i->item . ': </b>' . $i->quantity . '</li>';
                }
              }
            }
          echo '</ul>
        </li>
        <!-- topping section -->
        <li class="list-group-item" style="padding-bottom:0">
          <tstyle style="font-size: 16px">
            Toppings
          </tstyle>
          <ul class="list-group" style="padding-top: 5px">
            <!-- generate a <li> for each topping -->';
            $sql = "SELECT * FROM wp_entries, wp_items WHERE wp_entries.item = wp_items.name AND wp_entries.orderID = " . $o->id;
            $items = $wpdb->get_results($sql);
            if ($items[0] != NULL) {
              foreach($items as $i) {
                if ($i->main_item == FALSE) {
                  echo '<li class="list-group-item"><b>' . $i->item . ': </b>' . $i->quantity . '</li>';
                }
              }
            }
          echo '</ul>
        </li>
      </ul>
    </div>';
  }
}
?>
