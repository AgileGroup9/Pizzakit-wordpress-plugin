<?php

class Pizzakit_Blocks {
	public static function register_blocks() {
		Pizzakit_Blocks::register_order_form();
	}

	private static function register_order_form() {
		$asset_file = include plugin_dir_path(__FILE__) . './../order-form/index.asset.php';

		wp_register_script(
			'pizzakit-order-form-js',
			plugin_dir_url(__FILE__) . '../order-form/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		register_block_type(
			'pizzakit/order-form',
			array(
				'editor_script' => 'pizzakit-order-form-js',
				'render_callback' => 'Pizzakit_Blocks::render_order_form',
				'script' => 'pizzakit-order-form-js'
			)
		);
	}
	public static function render_order_form($attributes, $content) {
		global $wpdb;
		$sql = "SELECT * FROM wp_items";
		$items = $wpdb->get_results($sql);
		$json = json_encode($items);

		return '<script>window.pizzakitItems = ' . $json . ';</script><div id="pizzakit-order-form"></div>';
	}
}

?>