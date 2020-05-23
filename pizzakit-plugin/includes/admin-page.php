<?php
/* Template Name: Admin page */
global $wpdb;

if (post_password_required()) {
	echo(get_the_password_form());
	die();
}

if (isset($_POST["done"])) {
  $table = $wpdb->prefix . 'orders';
  $data = array("done" => TRUE);
  $where = array("id" => $_POST["done"]);
  $format = array("%d");
  $where_format = array("%d");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

if (isset($_POST["redo"])) {
  $table = $wpdb->prefix . 'orders';
  $data = array("done" => FALSE);
  $where = array("id" => $_POST["redo"]);
  $format = array("%d");
  $where_format = array("%d");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

if (isset($_POST["delete"])) {
  $table = $wpdb->prefix . 'orders';
  $where = array("id" => $_POST["delete"]);
  $where_format = array("%d");
  $wpdb->delete($table, $where, $where_format);
}

// handle an incoming POST object with items to delete from db
if (isset($_POST["deleteItem"])) {
  $sql = "SELECT list_order FROM " . $wpdb->prefix . "items WHERE name = '" . $_POST["deleteItem"] . "'";
  $listorder = $wpdb->get_results($sql);
  $table = $wpdb->prefix . 'items';
  $where = array("name" => $_POST["deleteItem"]);
  $where_format = array("%s");
  $wpdb->delete($table, $where, $where_format);
  $sql = "UPDATE " . $wpdb->prefix . "items SET list_order = list_order-1 WHERE list_order > " . $listorder[0]->list_order;
  $wpdb->query($sql);
}

// For handling additions to wp-items from "Ändra meny"
if (isset($_POST["addItem"])) {
  $maxorder = $wpdb->query("SELECT * FROM " . $wpdb->prefix . "items");
  $table = $wpdb->prefix . 'items';
  $data = array('name' => $_POST["addItemName"], 'price' => $_POST["addItemPrice"], 'comment' => $_POST["addItemComment"], 'list_order' => $maxorder);
  $where_format = array('%s', '%d', '%s', '%d');
  $wpdb->insert($table, $data, $where_format);
}

// For handling edits to wp-items from "Ändra meny"
if (isset($_POST["saveItem"])) {
  $table = $wpdb->prefix . 'items';
  $data = array('name' => $_POST["saveItemName"], 'price' => $_POST["saveItemPrice"], 'comment' => $_POST["saveItemComment"]);
  $where = array("name" => $_POST["saveItem"]);
  $format = array('%s', '%d', '%s');
  $where_format = array('%s');
  $wpdb->update($table, $data, $where, $where_format);
}

// handle an incoming POST object with item to activate in db
if (isset($_POST["activateItem"])) {
  $table = $wpdb->prefix . 'items';
  $data = array("isActive" => TRUE);
  $where = array("name" => $_POST["activateItem"]);
  $format = array("%s");
  $where_format = array("%s");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

// handle an incoming POST object with item to deactivate in db
if (isset($_POST["deactivateItem"])) {
  $table = $wpdb->prefix . 'items';
  $data = array("isActive" => FALSE);
  $where = array("name" => $_POST["deactivateItem"]);
  $format = array("%s");
  $where_format = array("%s");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

// handle an incoming POST object with item to make main item in db
if (isset($_POST["makeMain"])) {
  $table = $wpdb->prefix . 'items';
  $data = array("main_item" => TRUE);
  $where = array("name" => $_POST["makeMain"]);
  $format = array("%s");
  $where_format = array("%s");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

// handle an incoming POST object with item to unmake main item in db
if (isset($_POST["unmakeMain"])) {
  $table = $wpdb->prefix . 'items';
  $data = array("main_item" => FALSE);
  $where = array("name" => $_POST["unmakeMain"]);
  $format = array("%s");
  $where_format = array("%s");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

// handle an incoming POST object with item to move up in list
if (isset($_POST["moveUp"])) {
  // retrieve row above
  $sql = "SELECT name FROM " . $wpdb->prefix . "items WHERE list_order = " . ($_POST["moveUp"]-1);
  $previous = $wpdb->get_results($sql);
  // move item up
  $table = $wpdb->prefix . 'items';
  $data = array("list_order" => ($_POST["moveUp"]-1));
  $where = array("list_order" => $_POST["moveUp"]);
  $format = array("%d");
  $where_format = array("%d");
  $wpdb->update($table, $data, $where, $format, $where_format);
  // move item down
  $data = array("list_order" => ($_POST["moveUp"]));
  $where = array("name" => $previous[0]->name);
  $where_format = array("%s");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

// handle an incoming POST object with item to move up in list
if (isset($_POST["moveDown"])) {
  // retrieve row under
  $sql = "SELECT name FROM " . $wpdb->prefix . "items WHERE list_order = " . ($_POST["moveDown"]+1);
  $previous = $wpdb->get_results($sql);
  // move item down
  $table = $wpdb->prefix . 'items';
  $data = array("list_order" => ($_POST["moveDown"]+1));
  $where = array("list_order" => $_POST["moveDown"]);
  $format = array("%d");
  $where_format = array("%d");
  $wpdb->update($table, $data, $where, $format, $where_format);
  // move item up
  $data = array("list_order" => ($_POST["moveDown"]));
  $where = array("name" => $previous[0]->name);
  $where_format = array("%s");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

// delete every order that is older than 2 weeks
// using wpdb::query is safe since no user inputs are used
if (isset($_POST["clearAllOldOrders"])) {
  $wpdb->query('DELETE from ' . $wpdb->prefix . 'orders WHERE (14 <= (SELECT DATEDIFF(CURRENT_TIMESTAMP, date) AS dd));');
}

if (isset($_POST["export"])) {

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=backup-' . date("Y/m/d") . '.csv');

  // Opens output stream
  $output = fopen("php://output", "w");

  // Title row for order table
  $array = array('ID', 'Plats', 'Email', 'Telefon', 'Namn', 'Kommentar', 'Timestamp', 'Klar', 'UUID', 'Status');

  // SQL-query for complete orderinfo   
  $orders = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'orders', "ARRAY_A");

  // Outputs title rows and order info to CSV and pushes download
  fputcsv($output, array('wp-orders:'));
  fputcsv($output, $array);
  foreach ($orders as $r) {
    fputcsv($output, $r);
  }

  // Creates title row for entries table
  $array = array('orderID', 'item', 'quantity');

  // SQL query from wp-entries
  $entries = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'entries', "ARRAY_A");

  // Outputs title row and entries info for second table, with break row
  fputcsv($output, array());
  fputcsv($output, array('wp-entries:'));
  fputcsv($output, $array);

  foreach ($entries as $e) {
    fputcsv($output, $e);
  }

  // Closes output stream, kills site to supress HTML output
  fclose($output);
  die();
}

// Updates the settings for when the window for placing order start.
if (isset($_POST["update-start-time"])) {
  update_site_option('pizzakit_time_start_weekday', $_POST["weekday"]);
  update_site_option('pizzakit_time_start_hours', $_POST["hours"]);
}

// Updates the settings for when the window for placing order ends.
if (isset($_POST["update-end-time"])) {
  update_site_option('pizzakit_time_end_weekday', $_POST["weekday"]);
  update_site_option('pizzakit_time_end_hours', $_POST["hours"]);
}

// Updates the settings for pickup day.
if (isset($_POST["update-pickup-time"])) {
  update_site_option('pizzakit_time_pickup_start_day', $_POST["start"]);
  update_site_option('pizzakit_time_pickup_end_day', $_POST["end"]);
}

?>

<!-- import bootstrap css -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<?php
// Generate edit menu
if ($_POST["page"] == "edit-menu") {
  echo '<nav class="navbar navbar-inverse">
          <div class="container-fluid">
            <div class="navbar-header">
              <a class="navbar-brand" href="#">Verktyg</a>
            </div>
            <ul class="nav navbar-nav">
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="orders">
                  <input type="submit" class="btn btn-secondary" value="Nya ordrar">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn btn-primary" value="Ändra meny">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="all-orders">
                  <input type="submit" class="btn btn-secondary" value="Alla ordrar">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="export">
                  <input type="submit" class="btn btn-secondary" value="Exportera">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post" class="form-inline mr-auto">
                <input type="hidden" name="page" value="all-orders">
                <input type="text" class="form-control" name="order-search" placeholder="Namn, mailadress eller ID" style="min-width:250px">
                <input type="submit" class="btn btn-secondary" value="Sök">
              </form></li>
            </ul>
          </div>
        </nav>
      ';

  function weekdayOptions($selected) {
    $weekdays = array('Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag', 'Söndag');
    for ($i=1; $i <= count($weekdays); $i++) {
      echo('<option value="' . $i . '" ' . ($i == $selected ? 'selected' : '') . '>' . $weekdays[$i - 1] . '</option>');
    }
  }
  function hoursOptions($selected) {
    for ($i=0; $i <= 24; $i++) {
      echo("<option value=\"$i\" " . ($i == $selected ? 'selected' : '') . ">$i</option>");
    }
  }

  ?>
    <div class="container">
      <h3 align="center">Beställningsfönster:</h3>
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-4">
            <form class="form-inline" action="." method="post">
              <h4>Orderstart:</h4>
              <div class="form-group">
                <label for="start-weekday">Dag:</label>
                <select id="start-weekday" class="custom-select" name="weekday">
                  <?php weekdayOptions(get_site_option('pizzakit_time_start_weekday')); ?>
                </select>
              </div>
              <div class="form-group">
                <label for="start-hours">Timme:</label>
                <select id="start-hours" class="custom-select" name="hours">
                  <?php hoursOptions(get_site_option('pizzakit_time_start_hours')); ?>
                </select>
              </div>
              <input type="hidden" name="page" value="edit-menu">
              <input type="submit" class="btn-xs btn-primary" name="update-start-time" value="Uppdatera" />
            </form>
          </div>
          <div class="col-sm-4">
            <form class="form-inline" action="." method="post">
              <h4>Orderstopp:</h4>
              <div class="form-group">
                <label for="end-weekday">Dag:</label>
                <select id="end-weekday" class="custom-select" name="weekday">
                  <?php weekdayOptions(get_site_option('pizzakit_time_end_weekday')); ?>
                </select>
              </div>
              <div class="form-group">
                <label for="end-hours">Timme:</label>
                <select id="end-hours" class="custom-select" name="hours">
                  <?php hoursOptions(get_site_option('pizzakit_time_end_hours')); ?>
                </select>
              </div>
              <input type="hidden" name="page" value="edit-menu">
              <input type="submit" class="btn-xs btn-primary" name="update-end-time" value="Uppdatera" />
            </form>
          </div>
          <div class="col-sm-4">
            <form class="form-inline" action="." method="post">
            <h4>Upphämntningsdag:</h4>
              <div class="form-group">
                <label for="pickup-start-day">Från:</label>
                <select id="pickup-start-day" class="custom-select" name="start">
                  <?php weekdayOptions(get_site_option('pizzakit_time_pickup_start_day')); ?>
                </select>
              </div>
              <div class="form-group">
                <label for="pickup-end-day">Till:</label>
                <select id="pickup-end-day" class="custom-select" name="end">
                  <?php weekdayOptions(get_site_option('pizzakit_time_pickup_end_day')); ?>
                </select>
              </div>
              <input type="hidden" name="page" value="edit-menu">
              <input type="submit" class="btn-xs btn-primary" name="update-pickup-time" value="Uppdatera" />
            </form>
          </div>
        </div>
      </div>
    </div>
    <hr />
  <?php

  $sql = "SELECT * FROM " . $wpdb->prefix . "items ORDER BY list_order ASC";
  $items = $wpdb->get_results($sql);

echo '<ul class="list-group">
  <li class="list-group-item">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-r col-xs-2">
          <b>Namn</b>
        </div>
        <div class="col-sm-r col-xs-1">
          <b>Pris</b>
        </div>
        <div class="col-sm-r col-xs-2">
          <b>Kommentar</b>
        </div>
        <div class="col-sm-r col-xs-5">
          <b>Åtgärder</b>
        </div>
      </div>
    </div>
  </li>
';

$maxorder = $wpdb->query("SELECT * FROM " . $wpdb->prefix . "items")-1;

foreach ($items as $i) {
echo
'<li class="list-group-item">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-2 col-xs-2" style="font-size:16px">
        ' . $i->name .
'</div>
      <div class="col-sm-1 col-xs-1" style="font-size:16px">
        ' . $i->price . 'kr
      </div>
      <div class="col-sm-2 col-xs-2" style="font-size:16px">
        ' . $i->comment . '
      </div>
      <div class="col-sm-5 col-xs-5" style="font-size:16px">
        <div class="row">
          <div class="col-sm-1">';
            if ($i->list_order != 0) {
              echo '
                <form action="." method="post">
                    <input type="hidden" name="moveUp" value="' . $i->list_order . '">
                    <input type="hidden" name="page" value="edit-menu">
                    <input type="submit" class="btn-xs btn-primary pull-left" value="Upp">
                </form>';
            } echo '
          </div>
          <div class="col-sm-1">';
            if ($i->list_order != $maxorder) {
              echo '
                <form action="." method="post">
                    <input type="hidden" name="moveDown" value="' . $i->list_order . '">
                    <input type="hidden" name="page" value="edit-menu">
                    <input type="submit" class="btn-xs btn-dark pull-left" value="Ned">
                </form>';
            } echo '
          </div>
          <div class="col-sm-2">
            <form action="." method="post">';

// If the item is disabled, generate activate buttons
if ($i->isActive == 0) {
echo '
                <input type="hidden" name="activateItem" value="' . $i->name . '">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn-xs btn-success pull-left" value="Aktivera">
              ';

// If the item is enabled, generate deactivate button
} else {
echo '
                <input type="hidden" name="deactivateItem" value="' . $i->name . '">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn-xs btn-warning pull-left" value="Avaktivera">
              ';
}

echo '
            </form>
          </div>
            <div class="col-sm-2">
              <form action="." method="post">';

// If the item is not main, generate make main buttons
if ($i->main_item == 0) {
echo '
                <input type="hidden" name="makeMain" value="' . $i->name . '">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn-xs btn-secondary pull-left" value="Till pizzakit">
              ';

// If the item is main, generate deactivate main button
} else {
echo '
                <input type="hidden" name="unmakeMain" value="' . $i->name . '">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn-xs btn-info pull-left" value="Till tillbehör">
              ';
}

echo '
            </form>
          </div>
          <div class="col-sm-2">
            <form action="." method="post">
              <input type="hidden" name="editItem" value="' . $i->name . '">
              <input type="hidden" name="page" value="edit-menu">
              <input type="submit" class="btn-xs btn-success pull-left" value="Redigera">
            </form>
          </div>
          <div class="col-sm-2">
            <form action="." method="post">
              <input type="hidden" name="deleteItem" value="' . $i->name . '">
              <input type="hidden" name="page" value="edit-menu">
              <input type="submit" class="btn-xs btn-danger pull-left" value="Radera">
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</li>';
}

  //Adds a row with input fields for adding or editing items to wp-items. Handled at top of page.
  if (isset($_POST["editItem"])) {
    $sql = "SELECT * FROM " . $wpdb->prefix . "items WHERE name = '" . $_POST["editItem"] . "'";
    $result = $wpdb->get_results($sql);
    echo '
      <li class="list-group-item">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-2 col-xs-2 col-md-2 col-lg-2" style="font-size:16px">
              <form action="." method="post"><input class="form-control" type="text" name="saveItemName" value="' . $result[0]->name . '">
            </div>
            <div class="col-sm-2 col-xs-2 col-md-2 col-lg-2" style="font-size:16px">
              <input class="form-control" type="text" name="saveItemPrice" value="' . $result[0]->price . '">
            </div>
            <div class="col-sm-2 col-xs-2 col-md-2 col-lg-2" style="font-size:16px">
              <input class="form-control" type="text" name="saveItemComment" value="' . $result[0]->comment . '">
            </div>
            <div class="col-sm-2 col-xs-2 col-md-2 col-lg-2" style="font-size:16px">
                <input type="hidden" name="saveItem" value="' . $_POST["editItem"] . '">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn-sm btn-success pull-left" value="Spara">
              </form>
            </div>
          </div>
        </div>
      </li>';
  } else {
      echo '
        <li class="list-group-item">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-2 col-xs-2 col-md-2 col-lg-2" style="font-size:16px">
                <form action="." method="post"><input class="form-control" type="text" name="addItemName" placeholder="Namn">
              </div>
              <div class="col-sm-2 col-xs-2 col-md-2 col-lg-2" style="font-size:16px">
                <input class="form-control" type="text" name="addItemPrice" placeholder="Pris (kr)">
              </div>
              <div class="col-sm-2 col-xs-2 col-md-2 col-lg-2" style="font-size:16px">
                <input class="form-control" type="text" name="addItemComment" placeholder="Kommentar">
              </div>
              <div class="col-sm-2 col-xs-2 col-md-2 col-lg-2" style="font-size:16px">
                  <input type="hidden" name="addItem" value="TRUE">
                  <input type="hidden" name="page" value="edit-menu">
                  <input type="submit" class="btn-sm btn-success pull-left" value="Lägg till">
                </form>
              </div>
            </div>
          </div>
        </li>';
  }
  echo '</ul>';
}

// Generate the all orders-page
elseif ($_POST["page"] == "all-orders") {
  echo '<nav class="navbar navbar-inverse">
          <div class="container-fluid">
            <div class="navbar-header">
              <a class="navbar-brand" href="#">Verktyg</a>
            </div>
            <ul class="nav navbar-nav">
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="orders">
                  <input type="submit" class="btn btn-secondary" value="Nya ordrar">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn btn-secondary" value="Ändra meny">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="all-orders">
                  <input type="submit" class="btn btn-primary" value="Alla ordrar">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="export">
                  <input type="submit" class="btn btn-secondary" value="Exportera">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post" class="form-inline mr-auto">
                <input type="hidden" name="page" value="all-orders">
                <input type="text" class="form-control" name="order-search" style="min-width:250px" placeholder="';
  if (isset($_POST["order-search"])) {
    echo $_POST["order-search"];
  } else echo "Namn, mailadress eller ID";
  echo '">
                <input type="submit" class="btn btn-secondary" value="Sök">
              </form></li>
            </ul>
          </div>
        </nav>';

  if (isset($_POST["order-search"])) {
    $sql = "SELECT * FROM " . $wpdb->prefix . "orders WHERE " . $wpdb->prefix . "orders.name LIKE '%" . $_POST["order-search"] . "%' OR " . $wpdb->prefix . "orders.id LIKE '%" . $_POST["order-search"] . "%' OR " . $wpdb->prefix . "orders.email LIKE '%" . $_POST["order-search"] . "%'";
  } else {
    $sql = "SELECT * FROM " . $wpdb->prefix . "orders";
  }
  $orders = $wpdb->get_results($sql);

  if (!empty($orders)) {
    echo '<div class="row" style="padding-bottom:15px">
        <div class="col-lg-6 col-sm-6 col-md-6">
          <form action="." method="post" style="padding-top:15px;padding-left:90px">
            <input type="hidden" name="clearAllOldOrders" value="TRUE">
            <input type="hidden" name="page" value="all-orders">
            <input type="submit" class="btn btn-warning pull-left" value="Radera >2 veckor gamla ordrar" style="color:black">
          </form>
        </div>
      </div>';
    foreach ($orders as $o) {
      if ($o->done) {
        echo '<!-- One order block, generate one per order -->
          <div class="container-fluid"
              style="padding-left: 15px;padding-right: 15px;justify-content:center;width:90%">
              <ul class="list-group">
                  <!-- top section -->
                  <li class="list-group-item" style="min-height: 52px">
                      <div>
                          <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 22px">
                              <b>Order ID: </b>' . $o->id . '
                          </div>
                          <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                              <div class="btn-group pull-right" style="min-width:25px">
                                <form action="." method="post">
                                  <input type="hidden" name="delete" value="' . $o->id . '">
                                  <input type="hidden" name="page" value="all-orders">
                                  <input type="submit" class="btn-sm btn-danger" value="Radera">
                                </form>
                              </div>
                              <div class="btn-group pull-right" style="min-width:25px;margin-right:10px">
                                <form action="." method="post">
                                  <input type="hidden" name="redo" value="' . $o->id . '">
                                  <input type="hidden" name="page" value="all-orders">
                                  <input type="submit" class="btn-sm btn-secondary" value="Återaktivera">
                                </form>
                              </div>
                          </div>
                      </div>
                  </li>
                  <!-- topping section -->
                  <li class="list-group-item" style="padding-bottom:0;min-height:45px;padding-top:5px">
                      <div class="row">
                          <div class="col-sm-8 col-md-8 col-lg-8" style="padding-top:5px">
                              <tstyle style="font-size: 16px">
                                  <b>Kund:</b> ' . $o->name . '
                                  <b>Datum:</b> ' . $o->date . '
                                  <b>Mail:</b> ' . $o->email . '
                                  <b>Tel. nr.:</b> ' . $o->telNr . '
                                  <b>Status:</b> ' . ($o->status == 'PAID' ? 'Betald' : 'Obetald') . '
                              </tstyle>
                          </div>
                          <div class="col-sm-4 col-md-4 col-lg-4 pull-right" style="padding-top:0px;padding-bottom:5px">
                              <!-- Generate these for each order-->';
        $sql = "SELECT * FROM " . $wpdb->prefix . "entries WHERE orderID = " . $o->id;
        $items = $wpdb->get_results($sql);
        if ($items[0]) {
          $c = 1;
          foreach ($items as $i) {
            if ($c != 1)
              echo ', ';
            echo '<b>' . $i->item . ': </b>' . $i->quantity;
            $c++;
          }
        };
        echo '</div>
                      </div>
                  </li>';
        if ($o->comments) {
          echo '
                      <li class="list-group-item" style="padding-bottom:0;min-height:45px;padding-top:5px">
                        <div class="row">
                          <div class="col-sm-8 col-md-8 col-lg-8" style="padding-top:5px">
                            <tstyle style="font-size: 14px">
                                <b>Kommentar: </b>' . $o->comments . '
                            </tstyle>
                          </div>
                        </div>
                      </li>
                    ';
        }
        echo '</ul>
          </div>';
      } else {
        echo '<!-- One order block, generate one per order -->
          <div class="container-fluid"
              style="padding-left: 15px;padding-right: 15px;justify-content:center;width:90%">
              <ul class="list-group">
                  <!-- top section -->
                  <li class="list-group-item" style="min-height: 52px">
                      <div>
                          <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 22px">
                              <b>Order ID: </b>' . $o->id . '
                          </div>
                          <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                              <div class="btn-group pull-right" style="min-width:25px">
                                <form action="." method="post">
                                  <input type="hidden" name="done" value="' . $o->id . '">
                                  <input type="hidden" name="page" value="all-orders">
                                  <input type="submit" class="btn-sm btn-success" value="Klar">
                                </form>
                              </div>
                          </div>
                      </div>
                  </li>
                  <!-- topping section -->
                  <li class="list-group-item" style="padding-bottom:0;min-height:45px;padding-top:5px">
                      <div class="row">
                          <div class="col-sm-8 col-md-8 col-lg-8" style="padding-top:5px">
                              <tstyle style="font-size: 16px">
                                  <b>Kund:</b> ' . $o->name . '
                                  <b>Datum:</b> ' . $o->date . '
                                  <b>Mail:</b> ' . $o->email . '
                                  <b>Tel. nr.:</b> ' . $o->telNr . '
                                  <b>Status:</b> ' . ($o->status == 'PAID' ? 'Betald' : 'Obetald') . '
                              </tstyle>
                          </div>
                          <div class="col-sm-4 col-md-4 col-lg-4 pull-right" style="padding-top:0px;padding-bottom:5px">
                              <!-- Generate these for each order-->';
        $sql = "SELECT * FROM " . $wpdb->prefix . "entries, " . $wpdb->prefix . "items WHERE " . $wpdb->prefix . "entries.item = " . $wpdb->prefix . "items.name AND " . $wpdb->prefix . "entries.orderID = " . $o->id;
        $items = $wpdb->get_results($sql);
        if ($items[0]) {
          $c = 1;
          foreach ($items as $i) {
            if ($c != 1)
              echo ', ';
            echo '<b>' . $i->item . ': </b>' . $i->quantity;
            $c++;
          }
        };
        echo '</div>
                      </div>
                  </li>';
        if ($o->comments) {
          echo '
                      <li class="list-group-item" style="padding-bottom:0;min-height:45px;padding-top:5px">
                        <div class="row">
                          <div class="col-sm-8 col-md-8 col-lg-8" style="padding-top:5px">
                            <tstyle style="font-size: 14px">
                                <b>Kommentar: </b>' . $o->comments . '
                            </tstyle>
                          </div>
                        </div>
                      </li>
                    ';
        }
        echo '</ul>
          </div>';
      }
    }
  } else {
    echo '<h3 style="padding-left: 25px">Inga ordrar hittade</h3>';
  }
}

// Generate export/import-page
elseif ($_POST["page"] == "export") {

  echo '<nav class="navbar navbar-inverse">
          <div class="container-fluid">
            <div class="navbar-header">
              <a class="navbar-brand" href="#">Verktyg</a>
            </div>
            <ul class="nav navbar-nav">
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="orders">
                  <input type="submit" class="btn btn-secondary" value="Nya ordrar">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn btn-secondary" value="Ändra meny">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="all-orders">
                  <input type="submit" class="btn btn-secondary" value="Alla ordrar">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="export">
                  <input type="submit" class="btn btn-primary" value="Exportera">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post" class="form-inline mr-auto">
                <input type="hidden" name="page" value="all-orders">
                <input type="text" class="form-control" name="order-search" placeholder="Namn, mailadress eller ID" style="min-width:250px">
                <input type="submit" class="btn btn-secondary" value="Sök">
              </form></li>
            </ul>
          </div>
        </nav>
        <div class="container-fluid">
          <form method="post" action="." align="center">  
            <input type="submit" name="export" value="Exportera till CSV" class="btn btn-success" />
          </form>      
        </div>
        <form class="form-horizontal" action="." method="post"
                name="frmCSVImport" id="frmCSVImport"
                enctype="multipart/form-data">
                <div class="input-row">
                    <label class="col-md-4 control-label">Välj CSV-fil
                    </label> <input type="file" name="file"
                        id="file" accept=".csv">
                    <input type="hidden" name="page" value="export">
                    <button type="submit" id="submit" name="import"
                        class="btn-submit">Import</button>
                    <br />
                </div>
            </form>
      <div class="container" style="width:900px;align:left">   
      <h3 align="left">Orderdata</h3>                 
      <br />  
      <div class="table-responsive" id="employee_table" align="left">  
           <table class="table table-bordered">  
                <tr>  
                     <th width="10%">ID</th>
                     <th width="10%">Plats</th>
                     <th width="10%">Email</th>
                     <th width="10%">Telefon</th>
                     <th width="10%">Namn</th>
                     <th width="10%">Kommentar</th>
                     <th width="10%">Timestamp</th>
                     <th width="10%">Klar</th>
                     <th width="10%">UUID</th>
                     <th width="10%">Status</th>
                </tr>';
  $query = 'SELECT * FROM ' . $wpdb->prefix . "orders WHERE status='PAID'";
  $rows = $wpdb->get_results($query);
  foreach ($rows as $row) {
    echo '
                <tr>  
                     <td>' . $row->id . '</td>
                     <td>' . $row->location . '</td>
                     <td>' . $row->email . '</td>
                     <td>' . $row->telNr . '</td>
                     <td>' . $row->name . '</td>
                     <td>' . $row->comments . '</td>
                     <td>' . $row->date . '</td>
                     <td>' . $row->done . '</td>
                     <td>' . $row->uuid . '</td>
                     <td>' . $row->status . '</td>

                </tr>';
  }
  echo '
           </table>  
      </div>  
 </div>';
  if (isset($_POST["import"])) {
    echo '
 <div class="container" style="width:900px;align:left">   
 <h3 align="left">Importerade data</h3>                 
 <br />  
 <div class="table-responsive" id="employee_table" align="left">  
      <table class="table table-bordered">  
           <tr>  
                <th width="10%">ID</th>
                <th width="10%">Plats</th>
                <th width="10%">Email</th>
                <th width="10%">Telefon</th>
                <th width="10%">Namn</th>
                <th width="10%">Kommentar</th>
                <th width="10%">Timestamp</th>
                <th width="10%">Klar</th>
                <th width="10%">UUID</th>
                <th width="10%">Status</th>
           </tr>';

    $fileName = $_FILES["file"]["tmp_name"];

    if ($_FILES["file"]["size"] > 0) {
      $file = fopen($fileName, "r");
      fgetcsv($file, 10000, ",");
      fgetcsv($file, 10000, ",");
      echo '<h4 style="margin-top:0px">Orderinfo</h4>';
      while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
        if (count(array_filter($column)) == 0) {
          break;
        }
        echo '
            <tr>  
              <td>' . $column[0] . '</td>  
              <td>' . $column[1] . '</td>
              <td>' . $column[2] . '</td>
              <td>' . $column[3] . '</td>
              <td>' . $column[4] . '</td>
              <td>' . $column[5] . '</td>
              <td>' . $column[6] . '</td>
              <td>' . $column[7] . '</td>
              <td>' . $column[8] . '</td>
              <td>' . $column[9] . '</td>     
            </tr>';
      }
      echo '
      </table>
    </div>
    <div class="table-responsive" id="employee_table" align="left">  
      <table class="table table-bordered">  
         <tr>  
              <th width="20%">ID</th>  
              <th width="40%">Föremål</th>  
              <th width="40%">Kvantitet</th>     
         </tr>';
      fgetcsv($file, 10000, ",");
      fgetcsv($file, 10000, ",");
      echo '<h4>Orderinlägg</h4>';
      while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
        if (count(array_filter($column)) == 0) {
          break;
        }
        echo '
            <tr>  
              <td>' . $column[0] . '</td>  
              <td>' . $column[1] . '</td>
              <td>' . $column[2] . '</td>   
            </tr>  
          ';
      }
      echo '</table>
    </div>
    ';
    }
  }
}

// Generate the new orders-page
else {
  echo '
    <nav class="navbar navbar-inverse">
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">Verktyg</a>
        </div>
        <ul class="nav navbar-nav">
          <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
              <input type="hidden" name="page" value="orders">
              <input type="submit" class="btn btn-primary" value="Nya ordrar">
          </form></li>
          <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
            <input type="hidden" name="page" value="edit-menu">
            <input type="submit" class="btn btn-secondary" value="Ändra meny">
          </form></li>
          <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
            <input type="hidden" name="page" value="all-orders">
            <input type="submit" class="btn btn-secondary" value="Alla ordrar">
          </form></li>
          <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="export">
                  <input type="submit" class="btn btn-secondary" value="Exportera">
          </form></li>
          <li style="padding-top:10px; padding-left:10px"><form action="." method="post" class="form-inline mr-auto">
            <input type="hidden" name="page" value="all-orders">
            <input type="text" class="form-control" name="order-search" placeholder="Namn, mailadress eller ID" style="min-width:250px">
            <input type="submit" class="btn btn-secondary" value="Sök">
          </form></li>
        </ul>
      </div>
    </nav>';

  // Only load if there are > 0 orders in wp-orders
  if ($wpdb->query("SELECT * FROM " . $wpdb->prefix . "orders WHERE done = 0 AND status='PAID'") > 0) {
    // Get rows, and number of rows for table width. One redundant query here that could be removed
    $numberRows = $wpdb->query("SELECT * FROM " . $wpdb->prefix . "entries, " . $wpdb->prefix . "orders WHERE id = orderID AND done = 0 AND status = 'PAID' GROUP BY item");
    $rows = $wpdb->get_results("SELECT item, SUM(quantity) AS total_quantity FROM " . $wpdb->prefix . "entries, " . $wpdb->prefix . "orders WHERE id = orderID AND done = 0 AND status = 'PAID' GROUP BY item");

    // Outputs table that generates dynamically depending on amount of items currently in DB
    echo '
    <div class="container" style="width:1200px;" align="left">
      <h3 align="center" style="margin-top:0px">Sammanställning orderkvantitet</h3>
      <div class="table-responsive" id="summary_table" align="left">
        <table class="table table-bordered" align="left" style="text-align:center">
            <tr>';
    foreach ($rows as $r) {
      echo '<th style="text-align:center; font-size:12px" width="' . floor(100 / $numberRows) . '%">' . $r->item . '</th>';
    }
    echo '   
            </tr>
            <tr>';
    foreach ($rows as $r) {
      echo '<td>' . $r->total_quantity . '</td>';
    }
    echo '</tr>
        </table>
      </div>
    </div>
    ';
  }

  $sql = "SELECT * FROM " . $wpdb->prefix . "orders WHERE done = FALSE AND status='PAID'";
  $orders = $wpdb->get_results($sql);

  if (!empty($orders)) {
    foreach ($orders as $o) {
      echo '<!-- One order block, generate one per order -->
        <div class="container-fluid col-sm-12 col-md-6 col-lg-4">
          <ul class="list-group">
            <!-- top section -->
            <li class="list-group-item" style="min-height: 90px">
              <div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 22px">
                  <b>Order ID: </b> ' . $o->id . '
                    <p style="font-size: 14px">
                      <b> Upphämtas: </b>' . $o->location . '  
                      <br><b> Datum: </b>' . $o->date . '
                    </p>
                </div>
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                  <div class="btn-group pull-right" style="width:40px">
                    <form action="." method="post">
                      <input type="hidden" name="done" value="' . $o->id . '">
                      <input type="submit" class="btn-sm btn-success" value="Klar">
                    </form>
                  </div>
                </div>
              </div>
            </li>
            <!-- main items section -->
            <li class="list-group-item">';
      $sql = "SELECT * FROM " . $wpdb->prefix . "entries WHERE orderID = " . $o->id;
      $items = $wpdb->get_results($sql);
      if ($items[0]) {
        foreach ($items as $i) {
          echo '<b>' . $i->item . ': </b>' . $i->quantity . '<br>';
        }
      }
      echo  '</li>
              <li class="list-group-item">
                <div class="row">
                  <div class="col-sm-12 col-md-12 col-lg-12">
                    <tstyle style="font-size: 14px">';
      if ($o->comments) {
        echo '<b>Kommentar: </b>' . $o->comments . '<br>';
      }
      echo '<b>Kontakt:</b> ' . $o->name . ', ' . $o->email . ', ' . $o->telNr . '
                      </tstyle>
                    </div>
                  </div>
                </li>
              ';
      echo '</ul>
        </div>';
    }
  } else {
    echo '<h3 style="padding-left: 25px">Inga nya ordrar</h3>';
  }
}

?>
