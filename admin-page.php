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
?>

<!-- import bootstrap css -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<?php
if ($_POST["page"] == "edit-menu") {
  echo '<nav class="navbar navbar-inverse">
          <div class="container-fluid">
            <div class="navbar-header">
              <a class="navbar-brand" href="#">Verktyg</a>
            </div>
            <ul class="nav navbar-nav">
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="orders">
                  <input type="submit" class="btn btn-secondary" value="Ordrar">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn btn-primary" value="Ändra meny">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="history">
                  <input type="submit" class="btn btn-secondary" value="Historik">
              </form></li>
            </ul>
          </div>
        </nav>';
  echo "Theos kod";
}

elseif ($_POST["page"] == "history") {
  echo '<nav class="navbar navbar-inverse">
          <div class="container-fluid">
            <div class="navbar-header">
              <a class="navbar-brand" href="#">Verktyg</a>
            </div>
            <ul class="nav navbar-nav">
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="orders">
                  <input type="submit" class="btn btn-secondary" value="Ordrar">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                <input type="hidden" name="page" value="edit-menu">
                <input type="submit" class="btn btn-secondary" value="Ändra meny">
              </form></li>
              <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
                  <input type="hidden" name="page" value="history">
                  <input type="submit" class="btn btn-primary" value="Historik">
              </form></li>
            </ul>
          </div>
        </nav>';
        
  $sql = "SELECT * FROM wp_orders";
  $orders = $wpdb->get_results($sql);
  
  if ($orders[0] != NULL) {
    foreach($orders as $o) {
      if ($o->done == TRUE){
        echo '<!-- One order block, generate one per order -->
          <div class="container-fluid col-sm-12 col-md-6 col-lg-6"
              style="padding-top: 25px;padding-left: 15px;padding-right: 15px;">
              <ul class="list-group">
                  <!-- top section -->
                  <li class="list-group-item" style="min-height: 52px">
                      <div>
                          <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 22px">
                              <b>Order number: </b>' . $o->id . '
                          </div>
                          <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                              <div class="btn-group pull-right" style="min-width:25px">
                                <form action="." method="post">
                                  <input type="hidden" name="delete" value="' . $o->id . '">
                                  <input type="hidden" name="page" value="history">
                                  <input type="submit" class="btn-sm btn-danger" value="Radera">
                                </form>
                              </div>
                          </div>
                      </div>
                  </li>
                  <!-- topping section -->
                  <li class="list-group-item" style="padding-bottom:0;min-height:45px;padding-top:5px">
                      <div class="row">
                          <div class="col-sm-6 col-md-6 col-lg-6" style="padding-top:5px">
                              <tstyle style="font-size: 16px">
                                  <b>Ordered by:</b> ' . $o->name . '
                                  <b>Date:</b> ' . $o->date . '
                              </tstyle>
                          </div>
                          <div class="col-sm-6 col-md-6 col-lg-6 pull-right" style="padding-top:0px;padding-bottom:5px">
                              <!-- Generate these for each order-->';
                              $sql = "SELECT * FROM wp_entries, wp_items WHERE wp_entries.item = wp_items.name AND wp_entries.orderID = " . $o->id;
                              $items = $wpdb->get_results($sql);
                              if ($items[0] != NULL) {
                                foreach($items as $i) {
                                    echo '<b>' . $i->item . ': </b>' . $i->quantity . ', ';                                  
                                }
                              };          
                              echo '</div>
                      </div>          
                  </li>
              </ul>
          </div>';
      }
    }
  }
}

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
            <input type="submit" class="btn btn-primary" value="Ordrar">
        </form></li>
        <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
          <input type="hidden" name="page" value="edit-menu">
          <input type="submit" class="btn btn-secondary" value="Ändra meny">
        </form></li>
        <li style="padding-top:10px; padding-left:10px"><form action="." method="post">
            <input type="hidden" name="page" value="history">
            <input type="submit" class="btn btn-secondary" value="Historik">
        </form></li>
      </ul>
    </div>
  </nav>';

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
}
?>
