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

			// insert the data into the different tables
			// This assumes that the data is in a map-structure with the column names as keys
			insert_into_tables($data);

			// Respond with a JSON object by calling
			// wp_send_json($response);
			// where $response is an associative array.
	}


	private static function insert_into_tables($_data){

		//insert into orders
		$sql = "INSERT INTO wp_orders(email, name, telNr, address, doorCode, postalCode)
			VALUES ("
				. $_data['email'] . ", "
				. $_data['name'] . ", "
				. $_data['telNr'] . ", "
				. $_data['address'] . ", "
				. $_data['doorCode'] . ", "
				. $_data['postalCode'] . ",)";

		$wpdb->query($sql);

		//insert into entries
		$sql = "INSERT INTO wp_entries(orderID, item, quantity)
			VALUES (
				(SELECT MAX(id)+1 FROM wp_orders)), "
				. $_data['item'] . ", "
				. $_data['quantity'] . ")";

		$wpdb->query($sql);

	}
}

?>
