<?php

class Pizzakit_Activator {
	public static function activate() {

		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    /** Creating the database tables according to the following schema
    *
    * wp_orders(_id_, email, name, telNr, address, doorCode, postalCode date)
    *
    * wp_items (_name_, price)
    *
    * wp_entries(_order_, _item_, quantity)
    *   order -> wp_orders.id
    *   item -> wp_items.name
    */

    // WP_ORDERS
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $wpdb->prefix . 'orders') != $wpdb->prefix . 'orders') {
      $sql = 'CREATE TABLE ' . $wpdb->prefix . 'orders(
		    id INTEGER(10) UNSIGNED AUTO_INCREMENT,
		    email VARCHAR(100),
		    name TEXT,
				telNr VARCHAR(15),
				address TEXT,
				doorCode VARCHAR(10),
		    postalCode VARCHAR(6),
				comments TEXT,
		    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id)
      )';

      dbDelta($sql);
      add_option('orders_version','1.0');
    }

    // WP_ITEMS
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $wpdb->prefix . 'items') != $wpdb->prefix . 'items') {
      $sql = 'CREATE TABLE ' . $wpdb->prefix . 'items(
        name VARCHAR(25) NOT NULL,
        price INT NOT NULL,
  			PRIMARY KEY (name)
      )';

      dbDelta($sql);
      add_option('items_version','1.0');
    }

    // WP_ENTRIES
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $wpdb->prefix . 'entries') != $wpdb->prefix . 'entries') {
      $sql = 'CREATE TABLE ' . $wpdb->prefix . 'entries(
        orderID INTEGER(10) UNSIGNED,
        item VARCHAR(15) NOT NULL,
        quantity INT NOT NULL,
        PRIMARY KEY(orderID, item),
        FOREIGN KEY (orderID) REFERENCES ' . $wpdb->prefix . 'orders(id),
        FOREIGN KEY (item) REFERENCES ' . $wpdb->prefix . 'items(name)
      )';

      dbDelta($sql);
      add_option('entries_version','1.0');
    }
	}
}

?>
