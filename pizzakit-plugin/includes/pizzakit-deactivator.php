<?php

class Pizzakit_Deactivator
{
	public static function deactivate()
	{
		// dropping tables
		global $wpdb;
		$table_arr = ['items', 'entries', 'orders', 'passwords'];
		foreach ($table_arr as $table) {
			$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . $table;
			$wpdb->query($sql);
		}
	}
}
