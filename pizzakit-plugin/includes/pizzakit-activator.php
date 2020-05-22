<?php

class Pizzakit_Activator
{
	private static function add_admin_page()
	{
		global $wpdb;
		if (null === $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'pk_admin_page'", 'ARRAY_A')) {
			$current_user = wp_get_current_user();

			// create post object
			$page = array(
				'post_name' => 'pk_admin_page',
				'post_title'  => __('Pizzakit Admin Page'),
				'post_status' => 'publish',
				'post_author' => $current_user->ID,
				'post_type'   => 'page',
			);
			$new_page_id = wp_insert_post($page);
		}
	}

	public static function activate()
	{
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		/** Creating the database tables according to the following schema
		 *
		 * wp_orders(_id_, location, email, telNr, name, comments, date, done, uuid, status)
		 *
		 * wp_items (_name_, list_order, price, comment, main_item, isActive)
		 *
		 * wp_entries(_order_, _item_, quantity)
		 *   order -> wp_orders.id
		 *   item -> wp_items.name
		 */

		// WP_ORDERS
		if ($wpdb->get_var('SHOW TABLES LIKE ' . $wpdb->prefix . 'orders') != $wpdb->prefix . 'orders') {
			$sql = 'CREATE TABLE ' . $wpdb->prefix . 'orders(
			id INT UNSIGNED AUTO_INCREMENT,
			location TEXT,
			email VARCHAR(100),
			telNr VARCHAR(15),
			name TEXT,
			comments TEXT,
			date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			done BOOLEAN DEFAULT false,
			uuid VARCHAR(32) NOT NULL,
			status VARCHAR(30) NOT NULL,
			PRIMARY KEY (id)
		  )';

			dbDelta($sql);
			add_option('orders_version', '1.0');
		}

		// WP_ITEMS
		if ($wpdb->get_var('SHOW TABLES LIKE ' . $wpdb->prefix . 'items') != $wpdb->prefix . 'items') {
			$sql = 'CREATE TABLE ' . $wpdb->prefix . 'items(
			name VARCHAR(100) NOT NULL,
			list_order INT UNSIGNED DEFAULT 99,
			price INT UNSIGNED NOT NULL CHECK (price > 0),
			comment TEXT,
			main_item BOOLEAN DEFAULT FALSE,
			isActive BOOLEAN NOT NULL DEFAULT 1,
			PRIMARY KEY (name)
			)';

			dbDelta($sql);
			add_option('items_version', '1.0');
		}

		// WP_ENTRIES
		if ($wpdb->get_var('SHOW TABLES LIKE ' . $wpdb->prefix . 'entries') != $wpdb->prefix . 'entries') {
			$sql = 'CREATE TABLE ' . $wpdb->prefix . 'entries(
			orderID INT UNSIGNED REFERENCES ' . $wpdb->prefix . 'orders(id),
			item VARCHAR(100) NOT NULL,
			quantity INT UNSIGNED NOT NULL CHECK (quantity > 0),
			PRIMARY KEY(orderID, item),
			FOREIGN KEY(orderID) REFERENCES ' . $wpdb->prefix . 'orders(id) ON DELETE CASCADE ON UPDATE CASCADE
			)';

			dbDelta($sql);
			add_option('entries_version', '1.0');
		}

		// WP_PASSWORDS
		if ($wpdb->get_var('SHOW TABLES LIKE ' . $wpdb->prefix . 'passwords') != $wpdb->prefix . 'passwords') {
			$sql = 'CREATE TABLE ' . $wpdb->prefix . 'passwords(
			password VARCHAR(100) NOT NULL,
			PRIMARY KEY(password)
			)';

			dbDelta($sql);
			add_option('passwords_version', '1.0');
		}

		$table = $wpdb->prefix . 'passwords';
		$data_arr = array('password' => '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8');
		$format = array('%s');
		$wpdb->insert($table, $data_arr, $format);

	}
}
