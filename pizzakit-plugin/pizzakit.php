<?php

/**
 * @wordpress-plugin
 * Plugin Name:    Pizzakit
 * Plugin URI:     https://github.com/AgileGroup9/Pizzakit-wordpress-plugin
 * Description:    This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:        0.0.1
 * Author:         AgileGroup9
 * Author URI:     https://github.com/AgileGroup9/
 */

// If this file is called directly, abort.
if (!defined("WPINC")) {
	die;
}

/**
 * Currently plugin version.
 */
define("PIZZAKIT_VERSION", "0.0.1");

/**
 * The code that runs during plugin activation.
 */
function activate_pizzakit() {
	require_once plugin_dir_path(__FILE__) . "includes/pizzakit-activator.php";
	Pizzakit_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_pizzakit() {
	require_once plugin_dir_path(__FILE__) . "includes/pizzakit-deactivator.php";
	Pizzakit_Deactivator::deactivate();
}

register_activation_hook(__FILE__, "activate_pizzakit");
register_deactivation_hook(__FILE__, "deactivate_pizzakit");

function run_pizzakit() {
	require_once plugin_dir_path(__FILE__) . "includes/pizzakit.php";
	Pizzakit::run();
}
run_pizzakit();

?>