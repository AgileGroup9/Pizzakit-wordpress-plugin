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
	}

	private static function insert_into_tables($_data){

		global $wpdb;

		// Get the ID to use for this order
		$_result = $wpdb->get_results("SELECT MAX(id)+1 AS maxID FROM wp_orders", OBJECT);
		$_orderID	= $_result[0]->maxID;

		//insert into orders
		$sql = "INSERT INTO wp_orders(id, email, name, telNr, address, doorCode, postalCode, comments) VALUES ("
				. $_orderID . ", '"
				. $_data["email"] . "', '"
				. $_data["name"] . "', '"
				. $_data["telNr"] . "', '"
				. $_data["address"] . "', '"
				. $_data["doorCode"] . "', '"
				. $_data["postalCode"] . "', '"
				. $_data["comments"] . "')";
		$wpdb->query($sql);

		//insert into entries
		foreach ($_data["cart"] as $_item){
			$sql = "INSERT INTO wp_entries(orderID, item, quantity)
				VALUES ("
					. $_orderID . ", '"
					. $_item[0] . "', '"
					. $_item[1] . "')";

					$wpdb->query($sql);
		}
	}
}

?>
