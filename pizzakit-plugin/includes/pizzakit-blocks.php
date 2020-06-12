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

		wp_localize_script('pizzakit-order-form-js', 'WPURLS', array('siteurl' => get_option('siteurl') . '/'));
	}
	public static function render_order_form($attributes, $content) {
		global $wpdb;
		$sql = "SELECT * FROM " . $wpdb->prefix . "items";
		$items = $wpdb->get_results($sql);
		$json = json_encode($items);

		$times = array(
			'start' => array(
				'weekday' => get_site_option('pizzakit_time_start_weekday'),
				'hours' => get_site_option('pizzakit_time_start_hours')
			),
			'end' => array(
				'weekday' => get_site_option('pizzakit_time_end_weekday'),
				'hours' => get_site_option('pizzakit_time_end_hours')
			),
			'pickup' => array(
				'startDay' => get_site_option('pizzakit_time_pickup_start_day'),
				'endDay' => get_site_option('pizzakit_time_pickup_end_day')
			)
		);

		$sql = "SELECT * FROM " . $wpdb->prefix . "pickups";
		$pickups = $wpdb->get_results($sql);
		$json2 = json_encode($pickups);

		ob_start();
		?>
			<script>
				window.pizzakitItems = <?php echo($json); ?>;
				window.pizzakitTimes = <?php echo(json_encode($times)); ?>;
				window.pizzakitPickups = <?php echo($json2); ?>;
			</script>
			<div id="pizzakit-order-form">
				<p class="has-text-align-center">
					<strong>Pizzakit Orderformulär: Laddar...</strong>
				</p>
			</div>
		<?php
		return ob_get_clean();
	}
}

?>
