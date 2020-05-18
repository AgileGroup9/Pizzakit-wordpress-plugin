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

if (isset($_POST["delete"])) {
  $table = $wpdb->prefix . 'orders';
  $where = array("id" => $_POST["delete"]);
  $where_format = array("%d");
  $wpdb->delete($table, $where, $where_format);
}

// handle an incoming POST object with items to delete from db
if (isset($_POST["deleteItem"])) {
  $table = $wpdb->prefix . 'items';
  $where = array("name" => $_POST["deleteItem"]);
  $where_format = array("%s");
  $wpdb->delete($table, $where, $where_format);
}

// For handling additions to wp-items from "Ändra meny"
if (isset($_POST["addItem"])) {
  $table = $wpdb->prefix . 'items';
  $data = array('name'=>$_POST["addItemName"], 'price'=>$_POST["addItemPrice"], 'comment'=>$_POST["addItemComment"]);
  //$where = array("name" => $_POST["addItem"]);
  $where_format = array('%s','%d','%s');
  $wpdb->insert($table, $data, $where_format);
}

// handle an incoming POST object with items to activate in db
if (isset($_POST["activateItem"])) {
  $table = $wpdb->prefix . 'items';
  $data = array("isActive" => TRUE);
  $where = array("name" => $_POST["activateItem"]);
  $format = array("%s");
  $where_format = array("%s");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

// handle an incoming POST object with items to deactivate in db
if (isset($_POST["deactivateItem"])) {
  $table = $wpdb->prefix . 'items';
  $data = array("isActive" => FALSE);
  $where = array("name" => $_POST["deactivateItem"]);
  $format = array("%s");
  $where_format = array("%s");
  $wpdb->update($table, $data, $where, $format, $where_format);
}

// delete every order that is older than 2 weeks
// using wpdb::query is safe since no user inputs are used
if (isset($_POST["clearAllOldOrders"])){
  $wpdb->query('DELETE from ' . $wpdb->prefix . 'orders WHERE (14 <= (SELECT DATEDIFF(CURRENT_TIMESTAMP, date) AS dd));');
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
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post" class="form-inline mr-auto">
                <input type="hidden" name="page" value="all-orders">
                <input type="text" class="form-control" name="order-search" placeholder="Namn eller mailadress">
                <input type="submit" class="btn btn-secondary" value="Sök">
              </form></li>
            </ul>
          </div>
        </nav>
      <h3 style="padding-left: 25px">Ändra meny</h3>';

        $sql = "SELECT * FROM " . $wpdb->prefix . "items";
        $items = $wpdb->get_results($sql);

        echo '<ul class="list-group">
                <li class="list-group-item">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-sm-r col-xs-3">
                        <b>Namn</b>
                      </div>
                      <div class="col-sm-r col-xs-3">
                        <b>Pris</b>
                      </div>
                      <div class="col-sm-r col-xs-3">
                        <b>Kommentar</b>
                      </div>
                      <div class="col-sm-r col-xs-3">
                        <b>Åtgärder</b>
                      </div>
                    </div>
                  </div>
                </li>
              ';

        foreach ($items as $i) {
          echo
              '<li class="list-group-item">
                <div class="container-fluid">
                  <div class="row">
                    <div class="col-sm-3 col-xs-3" style="font-size:16px">
                      ' . $i->name .
                    '</div>
                    <div class="col-sm-3 col-xs-3" style="font-size:16px">
                      ' . $i->price . 'kr
                    </div>
                    <div class="col-sm-3 col-xs-3" style="font-size:16px">
                      ' . $i->comment . '
                    </div>
                    <div class="col-sm-3 col-xs-3" style="font-size:16px">
                      <div class="row">
                        <div class="col-sm-6">
                          <form action="." method="post">';

                          // If the item is disabled, generate activate buttons
                          if ($i->isActive == 0){
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
                        <div class="col-sm-6">
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

        //Adds a row with input fields for adding items to wp-items. Handled at top of page.
        echo '
        <li class="list-group-item">
                <div class="container-fluid">
                  <div class="row">
                    <div class="col-sm-3 col-xs-3 col-md-3 col-lg-3" style="font-size:16px">
                      <form action="." method="post"><input class="form-control" type="text" name="addItemName" placeholder="Namn">
                    </div>
                    <div class="col-sm-3 col-xs-3 col-md-3 col-lg-3" style="font-size:16px">
                      <input class="form-control" type="text" name="addItemPrice" placeholder="Pris (kr)">
                    </div>
                    <div class="col-sm-3 col-xs-3 col-md-3 col-lg-3" style="font-size:16px">
                      <input class="form-control" type="text" name="addItemComment" placeholder="Kommentar">
                    </div>
                    <div class="col-sm-3 col-xs-3 col-md-3 col-lg-3" style="font-size:16px">
                        <input type="hidden" name="addItem" value="TRUE">
                        <input type="hidden" name="page" value="edit-menu">
                        <input type="submit" class="btn-sm btn-success pull-left" value="Lägg till">
                      </form>
                    </div>
                  </div>
                </div>
              </li>';
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
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post" class="form-inline mr-auto">
                <input type="hidden" name="page" value="all-orders">
                <input type="text" class="form-control" name="order-search" placeholder="';
                if (isset($_POST["order-search"])) {
                  echo $_POST["order-search"];
                } else echo "Namn eller mailadress";
                echo '">
                <input type="submit" class="btn btn-secondary" value="Sök">
              </form></li>
            </ul>
          </div>
        </nav>';

  if (isset($_POST["order-search"])) {
    $sql = "SELECT * FROM " . $wpdb->prefix . "orders WHERE " . $wpdb->prefix . "orders.name LIKE '%" . $_POST["order-search"] . "%' OR " . $wpdb->prefix . "orders.email LIKE '%" . $_POST["order-search"] . "%'";
  } else {
    $sql = "SELECT * FROM " . $wpdb->prefix . "orders";
  }
  $orders = $wpdb->get_results($sql);

  if (!empty($orders)) {
    echo '<div class="row" style="padding-bottom:15px">
        <div class="col-lg-6 col-sm-6 col-md-6 pull-left">
          <h3 style="padding-left: 25px">Alla ordrar</h3>
        </div>
        <div class="col-lg-6 col-sm-6 col-md-6">
          <form action="." method="post" style="padding-top:15px;padding-right:25px">
            <input type="hidden" name="clearAllOldOrders" value="TRUE">
            <input type="hidden" name="page" value="all-orders">
            <input type="submit" class="btn btn-warning pull-right" value="Radera >2 veckor gamla ordrar" style="color:black">
          </form>
        </div>
      </div>';
    foreach($orders as $o) {
      if ($o->done == TRUE){
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
                              </tstyle>
                          </div>
                          <div class="col-sm-4 col-md-4 col-lg-4 pull-right" style="padding-top:0px;padding-bottom:5px">
                              <!-- Generate these for each order-->';
                              $sql = "SELECT * FROM " . $wpdb->prefix . "entries, " . $wpdb->prefix . "items WHERE " . $wpdb->prefix . "entries.item = " . $wpdb->prefix . "items.name AND " . $wpdb->prefix . "entries.orderID = " . $o->id;
                              $items = $wpdb->get_results($sql);
                              if ($items[0] != NULL) {
                                $c = 1;
                                foreach($items as $i) {
                                    if ($c!=1)
                                      echo ', ';
                                    echo '<b>' . $i->item . ': </b>' . $i->quantity;
                                    $c++;
                                }
                              };
                              echo '</div>
                      </div>
                  </li>
              </ul>
          </div>';
      }
      else {
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
                              </tstyle>
                          </div>
                          <div class="col-sm-4 col-md-4 col-lg-4 pull-right" style="padding-top:0px;padding-bottom:5px">
                              <!-- Generate these for each order-->';
                              $sql = "SELECT * FROM " . $wpdb->prefix . "entries, " . $wpdb->prefix . "items WHERE " . $wpdb->prefix . "entries.item = " . $wpdb->prefix . "items.name AND " . $wpdb->prefix . "entries.orderID = " . $o->id;
                              $items = $wpdb->get_results($sql);
                              if ($items[0] != NULL) {
                                $c = 1;
                                foreach($items as $i) {
                                    if ($c!=1)
                                      echo ', ';
                                    echo '<b>' . $i->item . ': </b>' . $i->quantity;
                                    $c++;
                                }
                              };
                              echo '</div>
                      </div>
                  </li>
              </ul>
          </div>';
      }
    }
  } else {
    echo '<h3 style="padding-left: 25px">Inga ordrar hittade</h3>';
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
          <li style="padding-top:10px; padding-left:10px"><form action="." method="post" class="form-inline mr-auto">
            <input type="hidden" name="page" value="all-orders">
            <input type="text" class="form-control" name="order-search" placeholder="Namn eller mailadress">
            <input type="submit" class="btn btn-secondary" value="Sök">
          </form></li>
        </ul>
      </div>
    </nav>';

  // Only load if there are > 0 orders in wp-orders
  if($wpdb->query("SELECT * FROM " . $wpdb->prefix . "orders") > 0){
    echo '<h3 style="padding-left: 25px">Sammanställning varuåtgång</h3>';
    
    // Get rows, and number of rows for table width. One redundant query here that could be removed
    $numberRows = $wpdb->query("SELECT * FROM " . $wpdb->prefix . "items");
    $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "items");

    // Initialize array with quantities of each item. Don't know if this is necessary tbh
    $quantities = array();
    foreach($rows as $r)
      $quantities[$r->name] = 0;

    // Get entries
    $entries = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "entries");

    // For each entry that is part of a "done" order, continue. Otherwise, add quantity to array
    foreach($entries as $e){
      if($wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "orders WHERE id = " . $e->orderID)[0]->done == 1){continue;}
        $quantities[$e->item] += $e->quantity;
    }
    // Outputs table that generates dynamically depending on amount of items currently in DB
    echo '
    <div class="container" style="width:900px;align:left;">                  
      <br />   
      <div class="table-responsive" id="summary_table" align="left">  
        <table class="table table-bordered">  
            <tr>';
              foreach($rows as $r){
                echo '<th width="' . floor(100/$numberRows) . '%">' . $r->name . '</th>';
              }
            echo '   
            </tr>
            <tr>';
              foreach($rows as $r){
                echo '<td>' . $quantities[$r->name] . '</td>';
              }
            echo '</tr>
        </table>
      </div>
    </div>
    ';
  }
  
  $sql = "SELECT * FROM " . $wpdb->prefix . "orders WHERE done = FALSE";
  $orders = $wpdb->get_results($sql);

  if (!empty($orders)) {
    echo '<h3 style="padding-left: 25px">Nya ordrar</h3>';
    foreach ($orders as $o) {
      echo '<!-- One order block, generate one per order -->
        <div class="container-fluid col-sm-12 col-md-6 col-lg-4">
          <ul class="list-group">
            <!-- top section -->
            <li class="list-group-item" style="min-height: 75px">
              <div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 22px">
                  <b>Order ID: </b> ' . $o->id . '
                  <br>
                    <p style="font-size: 15px">
                      <b>Adress: </b> ' . $o->address . ',' . $o->postalCode . '
                    </p>
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
                $sql = "SELECT * FROM " . $wpdb->prefix . "entries, " . $wpdb->prefix . "items WHERE " . $wpdb->prefix . "entries.item = " . $wpdb->prefix . "items.name AND " . $wpdb->prefix . "entries.orderID = " . $o->id;
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
                $sql = "SELECT * FROM " . $wpdb->prefix . "entries, " . $wpdb->prefix . "items WHERE " . $wpdb->prefix . "entries.item = " . $wpdb->prefix . "items.name AND " . $wpdb->prefix . "entries.orderID = " . $o->id;
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
  } else {
    echo '<h3 style="padding-left: 25px">Inga nya ordrar</h3>';
  }
}
?>
