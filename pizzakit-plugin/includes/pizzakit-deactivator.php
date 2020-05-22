<?php

class Pizzakit_Deactivator
{
	public static function deactivate()
	{
		// dropping tables
		global $wpdb;
		$table_arr = ['items', 'entries', 'orders'];
		foreach ($table_arr as $table) {
			$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . $table;
			$wpdb->query($sql);
		}
		
		// removing settings
		delete_site_option('pizzakit_time_start_weekday');
		delete_site_option('pizzakit_time_start_hours');
		delete_site_option('pizzakit_time_end_weekday');
		delete_site_option('pizzakit_time_end_hours');
		delete_site_option('pizzakit_time_pickup_start_day');
		delete_site_option('pizzakit_time_pickup_end_day');
	}
}
