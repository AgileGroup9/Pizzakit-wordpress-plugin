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
	}
}
