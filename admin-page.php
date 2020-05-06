<?php
/* Template Name: Admin page */
global $wpdb;
if (isset($_POST["done"])) {
  $table = $wpdb->prefix . 'orders';
  $data = array("done" => TRUE);
  $where = array("id" => $_POST["done"]);
  $format = array("%d");
  $where_format = array("%d");
  $wpdb->update($table, $data, $where, $format, $where_format);
}
?>

<!-- import bootstrap css -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<!-- Top navigation bar -->
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">Adminsidan</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="active"><a href="#">Ordrar</a></li>
      <li><a href="#">Ändra meny</a></li>
      <li><a href="history-page.php">Historik</a></li>
    </ul>
  </div>
</nav>

<?php
$sql = "SELECT * FROM wp_orders";
$orders = $wpdb->get_results($sql);

if ($orders[0] != NULL) {
  foreach ($orders as $o) {
    if ($o->done == FALSE) {
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
                <div class="btn-group pull-right" style="width:50px">
                  <form action="." method="post">
                    <input type="hidden" name="done" value="' . $o->id . '">
                    <input type="submit" class="btn-sm btn-success" value="Klar">
                  </form>
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
}
?>
