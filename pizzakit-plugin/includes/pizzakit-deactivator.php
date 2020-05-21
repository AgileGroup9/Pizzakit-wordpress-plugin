<?php

class Pizzakit_Deactivator
{
	public static function deactivate()
	{
		// dropping tables
		global $wpdb;
		$table_arr = ['items', 'entries', 'orders', 'payment'];
		foreach ($table_arr as $table) {
			$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . $table;
			$wpdb->query($sql);
		}

		//deleting admin-page
		$page = get_page_by_title("Pizzakit Admin Page");
		wp_delete_post($page->ID, true);
		
		// removing settings
		delete_site_option('pizzakit_time_start_weekday');
		delete_site_option('pizzakit_time_start_hours');
		delete_site_option('pizzakit_time_end_weekday');
		delete_site_option('pizzakit_time_end_hours');
	}
}
