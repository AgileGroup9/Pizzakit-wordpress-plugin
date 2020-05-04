<?php

class Pizzakit {
	public static function run() {
		add_action("init", "Pizzakit::init");
	}

	public static function init() {
		Pizzakit::handle_post();

		require_once plugin_dir_path( __FILE__ ) . 'pizzakit-blocks.php';
		Pizzakit_Blocks::register_blocks();
	}

	private static function handle_post() {

		$json = file_get_contents("php://input");
		$data = json_decode($json, true);

		// Create a "secret" JSON field that's only set when posting from our
		// form so that we don't respond to other post requests.
		// In this the example it's "pizzakitFormSubmission".
		if (isset($data["pizzakitFormSubmission"])) {

			Pizzakit::insert_into_tables($data);

			$response = array('orderPlaced' => true);
			wp_send_json($response);
		}

		if (isset($data["refresh_menu_items"])){

			Pizzakit::refresh_menu_items();

			$response = array('menu_items_refreshed' => true);
			wp_send_json($response);
		}
	}

	private static function insert_into_tables($_data){

		global $wpdb;

		//insert into orders, using insert() function to get it prepared
		$_table = $wpdb->prefix. 'orders';
		$_dataArr = array('id' => null,'email' => $_data["email"],'name' => $_data["name"],'telNr' => $_data["telNr"],
			'address' => $_data["address"], 'doorCode' => $_data["doorCode"], 'postalCode' => $_data["postalCode"], 'comments' => $_data["comments"]);
		$_format = array('%d','%s','%s','%s','%s','%s','%s','%s');
		$wpdb->insert($_table,$_dataArr,$_format);
		$_lastid = $wpdb->insert_id;

		//insert into entries
		foreach ($_data["cart"] as $_item){
			$_table = $wpdb->prefix. 'entries';
			$_dataArr = array('orderID' => $_lastid,'item'=>$_item[0],'quantity'=>$_item[1]);
			$_format = array('%d','%s','%d');
			$wpdb->insert($_table,$_dataArr,$_format);
		}
	}

	public static function refresh_menu_items(){
		global $wpdb;
		$table = $wpdb->prefix . 'items';
		$json = file_get_contents(plugin_dir_path(__FILE__) . 'items_for_sale.json');
		$data = json_decode($json, true);
		
		//drop all data in items-table
		$wpdb->query('TRUNCATE TABLE ' . $table);
		
		//insert items
		foreach ($data["main_items"] as $item){
			$data_arr = array('name' => $item["name"], 'price' => $item["price"], "comment" => $item["comment"], "main_item" => true);
			$format = array('%s','%d','%s','%d');
			$wpdb->insert($table,$data_arr,$format);
		}
		foreach ($data["extras"] as $extra) {
			$data_arr = array('name' => $extra["name"],'price' => $extra["price"],"comment" => $extra["comment"], "main_item" => false);
			$format = array('%s','%d','%s','%d');
			$wpdb->insert($table,$data_arr,$format);
		}
	}
}

?>
