<?php

class Pizzakit {
	public static function run() {
		add_action("enqueue_block_editor_assets", "Pizzakit::enqueue_blocks");
	}

	public static function enqueue_blocks() {
		wp_enqueue_script(
			"pizzakit-order-form-js",
			plugin_dir_url(__FILE__) . "../order-form/order-form.js",
			array("wp-blocks", "wp-editor"),
			true
		);
	}
}

?>