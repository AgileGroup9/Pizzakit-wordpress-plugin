<?php

class Pizzakit {
	public static function run() {
		add_action("init", "Pizzakit::init");
		add_action("enqueue_block_editor_assets", "Pizzakit::enqueue_blocks");
	}

	public static function init() {
		Pizzakit::handle_post();
	}

	public static function enqueue_blocks() {
		wp_enqueue_script(
			"pizzakit-order-form-js",
			plugin_dir_url(__FILE__) . "../order-form/order-form.js",
			array("wp-blocks", "wp-editor"),
			true
		);
	}

	private static function handle_post() {

		$json = file_get_contents("php://input");
		$data = json_decode($json, true);

		// Create a "secret" JSON field that's only set when posting from our
		// form so that we don't respond to other post requests.
		// In this the example it's "pizzakitFormSubmission".
		if (isset($data["pizzakitFormSubmission"])) {

			Pizzakit::insert_into_tables($data);

			// Respond with a JSON object by calling
			// wp_send_json($response);
			// where $response is an associative array.
		}

		// Send a JSON object with the tag "addItemsToMenu"=true
		// and an array of [itemname, price] to add them to the
		// wp_items database table
		if (isset($data["addItemsToMenu"])){

			Pizzakit::add_menu_items($data);
		}

		// Send a JSON object with the tag "removeItemsFromMenu"=true
		// and an array of item names to remove them from the
		// wp_items database table
		if (isset($data["removeItemsFromMenu"])){

			Pizzakit::remove_menu_items($data);
		}
	}

	private static function insert_into_tables($_data){

		global $wpdb;

		//insert into orders, using insert() function to get it prepared
		$table = $wpdb->prefix. 'orders';
		$data = array('id' => null,'email' => $_data["email"],'name' => $_data["name"],'telNr' => $_data["telNr"],
			'address' => $_data["address"], 'doorCode' => $_data["doorCode"], 'postalCode' => $_data["postalCode"], 'comments' => $_data["comments"]);
		$format = array('%d','%s','%s','%s','%s','%s','%s','%s');
		$wpdb->insert($table,$data,$format);
		$lastid = $wpdb->insert_id;

		//insert into entries
		foreach ($_data["cart"] as $_item){
			$table2 = $wpdb->prefix. 'entries';
			$data = array('orderID' => $lastid,'item'=>$_item[0],'quantity'=>$_item[1]);
			$format = array('%d','%s','%d');
			$wpdb->insert($table2,$data,$format);
		}
	}

	private static function add_menu_items($_data){

		global $wpdb;
		$table = $wpdb->prefix . 'items';

		//insert items
		foreach ($_data["items"] as $_item){
			$_dataArr = array('name' => $_item[0],'price'=>$_item[1]);
			$format = array('%s','%d');
			$wpdb->insert($table,$_dataArr,$format);
		}
	}

	private static function remove_menu_items($_data){

		global $wpdb;
		$table = $wpdb->prefix . 'items';

		//remove items
		foreach ($_data["items"] as $_item){
			$_where = array('name' => $_item);
			$wpdb->delete($table,$_where);
		}
	}

}

?>
