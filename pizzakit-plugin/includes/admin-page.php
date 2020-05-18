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

if (isset($_POST["export"])){
  
  header('Content-Type: text/csv; charset=utf-8');  
  header('Content-Disposition: attachment; filename=backup-' . date("Y/m/d") . '.csv');  
  
  // Opens output stream
  $output = fopen("php://output", "w");

  // Title row for order table
  $array = array('ID', 'Email', 'Namn', 'Telefon', 'Adress', 'Portkod', 'Postnummer', 'Kommentar', 'Timestamp', 'Klar');
  
  // SQL-query for complete orderinfo   
  $orders = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'orders',"ARRAY_A"); 

  // Outputs title rows and order info to CSV and pushes download
  fputcsv($output, array('wp-orders:'));
  fputcsv($output, $array);
  foreach($orders as $r)  
  {  
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
  
  foreach($entries as $e)  
  {  
    fputcsv($output, $e);  
  } 

  // Closes output stream
  fclose($output);

}
/*
if (isset($_POST["import"])){
  //Import code goes here
  echo 'test';

}
*/



if ($_POST["page"] == "edit-menu") {
  // <!-- import bootstrap css -->
  echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">';

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
                <input type="text" class="form-control" name="order-search" placeholder="Namn eller mailadress">
                <input type="submit" class="btn btn-secondary" value="Sök">
              </form></li>
            </ul>
          </div>
        </nav>
      <h3 style="padding-left: 25px">Ändra meny</h3>';

        $sql = "SELECT * FROM wp_items";
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

elseif ($_POST["page"] == "export"){
  // <!-- import bootstrap css -->
  echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">';

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
                <input type="text" class="form-control" name="order-search" placeholder="Namn eller mailadress">
                <input type="submit" class="btn btn-secondary" value="Sök">
              </form></li>
            </ul>
          </div>
        </nav>
      <h3 style="padding-left: 25px">Exportera och Importera</h3>
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
                     <th width="15%">Email</th>  
                     <th width="15%">Name</th>  
                     <th width="10%">Telefon</th>  
                     <th width="20%">Adress</th>
                     <th width="10%">Postkod</th>  
                     <th width="20%">Timestamp</th>    
                </tr>';  
                $query = 'SELECT * FROM '. $wpdb->prefix . 'orders';
                $rows = $wpdb->get_results($query);
                foreach($rows as $row)   
                {  
                  echo '
                <tr>  
                     <td>' . $row->id . '</td>  
                     <td>' . $row->email . '</td>
                     <td>' . $row->name . '</td>
                     <td>' . $row->telNr . '</td>
                     <td>' . $row->address . '</td>
                     <td>' . $row->postalCode . '</td>
                     <td>' . $row->date . '</td>     

                </tr>';  
                  
                }  
             echo '
           </table>  
      </div>  
 </div>';
if(isset($_POST["import"])){
 echo '
 <div class="container" style="width:900px;align:left">   
 <h3 align="left">Importerade data</h3>                 
 <br />  
 <div class="table-responsive" id="employee_table" align="left">  
      <table class="table table-bordered">  
           <tr>  
                <th width="10%">ID</th>  
                <th width="15%">Email</th>  
                <th width="15%">Name</th>  
                <th width="10%">Telefon</th>  
                <th width="20%">Adress</th>
                <th width="10%">Postkod</th>  
                <th width="20%">Timestamp</th>    
           </tr>
      

';

$fileName = $_FILES["file"]["tmp_name"];
    
    if ($_FILES["file"]["size"] > 0) {
        
        $file = fopen($fileName, "r");
        fgetcsv($file, 10000, ",");
        fgetcsv($file, 10000, ",");
        echo '<h4 style="margin-top:0px">Orderinfo</h4>';
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
          //print_r($column);
          if(count(array_filter($column)) == 0) {
            break;
          } 
          echo '
            <tr>  
              <td>' . $column[0] . '</td>  
              <td>' . $column[1] . '</td>
              <td>' . $column[2] . '</td>
              <td>' . $column[3] . '</td>
              <td>' . $column[4] . '</td>
              <td>' . $column[6] . '</td>
              <td>' . $column[8] . '</td>     
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
          //print_r($column);
          if(count(array_filter($column)) == 0) {
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

        // Tried to insert into DB using this code, let it stand for now for future reference
        /*
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
             print_r($column);
            $formatArray = array('%d','%s','%s','%s','%s','%s','%s','%s','%s','%d');
            
            $insertId = $wpdb->insert(wpdb->prefix . 'orders', $column);
            
            if (! empty($insertId)) {
                $type = "success";
                $message = "CSV Data Imported into the Database";
            } else {
                $type = "error";
                $message = "Problem in Importing CSV Data";
            }
            
        }*/
    }


}
}

elseif ($_POST["page"] == "all-orders") {
  // <!-- import bootstrap css -->
  echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">';

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
                <input type="text" class="form-control" name="order-search" placeholder="';
                if (isset($_POST["order-search"])) {
                  echo $_POST["order-search"];
                } else echo "Namn eller mailadress";
                echo '">
                <input type="submit" class="btn btn-secondary" value="Sök">
              </form></li>
            </ul>
          </div>
        </nav>
      <h3 style="padding-left: 25px">Alla ordrar</h3>';

  if (isset($_POST["order-search"])) {
    $sql = "SELECT * FROM wp_orders WHERE wp_orders.name LIKE '%" . $_POST["order-search"] . "%' OR wp_orders.email LIKE '%" . $_POST["order-search"] . "%'";
    $orders = $wpdb->get_results($sql);
  } else {
    $sql = "SELECT * FROM wp_orders";
    $orders = $wpdb->get_results($sql);
  }

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
                          <div class="col-sm-6 col-md-6 col-lg-6" style="padding-top:5px">
                              <tstyle style="font-size: 16px">
                                  <b>Kund:</b> ' . $o->name . '
                                  <b>Datum:</b> ' . $o->date . '
                                  <b>Mail:</b> ' . $o->email . '
                                  <b>Tel. nr.:</b> ' . $o->telNr . '
                              </tstyle>
                          </div>
                          <div class="col-sm-6 col-md-6 col-lg-6 pull-right" style="padding-top:0px;padding-bottom:5px">
                              <!-- Generate these for each order-->';
                              $sql = "SELECT * FROM wp_entries, wp_items WHERE wp_entries.item = wp_items.name AND wp_entries.orderID = " . $o->id;
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
          <div class="container-fluid col-sm-12 col-md-6 col-lg-6"
              style="padding-top: 25px;padding-left: 15px;padding-right: 15px;">
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
                          <div class="col-sm-6 col-md-6 col-lg-6" style="padding-top:5px">
                              <tstyle style="font-size: 16px">
                                  <b>Kund:</b> ' . $o->name . '
                                  <b>Datum:</b> ' . $o->date . '
                              </tstyle>
                          </div>
                          <div class="col-sm-6 col-md-6 col-lg-6 pull-right" style="padding-top:0px;padding-bottom:5px">
                              <!-- Generate these for each order-->';
                              $sql = "SELECT * FROM wp_entries, wp_items WHERE wp_entries.item = wp_items.name AND wp_entries.orderID = " . $o->id;
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
  }
}

elseif($_POST["page"] == "orders") {
  // <!-- import bootstrap css -->
  echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">';

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
            <input type="text" class="form-control" name="order-search" placeholder="Namn eller mailadress">
            <input type="submit" class="btn btn-secondary" value="Sök">
          </form></li>
        </ul>
      </div>
    </nav>
  <h3 style="padding-left: 25px">Nya ordrar</h3>';

  $sql = "SELECT * FROM wp_orders";
  $orders = $wpdb->get_results($sql);

  if ($orders[0] != NULL) {
    foreach ($orders as $o) {
      if ($o->done == FALSE) {
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
