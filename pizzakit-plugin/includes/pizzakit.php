<?php

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Pizzakit
{

	public static function run()
	{
		add_action("init", "Pizzakit::init");
		require_once plugin_dir_path(__FILE__) . 'pizzakit-page_templater.php';
		add_action('plugins_loaded', 'PageTemplater::get_instance');
		add_action('rest_api_init', function () {
			register_rest_route('pizzakit', '/callback/(?P<id>\d+)', array(
				'methods' => 'POST',
				'callback' => [__CLASS__, 'swish_callback_handler'],
			));
			register_rest_route('pizzakit', '/payment/(?P<id>\d+)', array(
				'methods' => 'GET',
				'callback' => [__CLASS__, 'payment_query_handler'],
			));
			register_rest_route('pizzakit', '/items', array(
				'methods' => 'GET',
				'callback' => [__CLASS__, 'item_query_handler'],
			));
		});
	}

	public static function init()
	{
		Pizzakit::handle_post();

		require_once plugin_dir_path(__FILE__) . 'pizzakit-blocks.php';
		Pizzakit_Blocks::register_blocks();
	}

	private static function handle_post()
	{

		$json = file_get_contents("php://input");
		$data = json_decode($json, true);

		// Create a "secret" JSON field that's only set when posting from our
		// form so that we don't respond to other post requests.
		// In this the example it's "pizzakitFormSubmission".
		if (isset($data["pizzakitFormSubmission"])) {

			//if there are negative item quantities, abort mission
			if (Pizzakit::is_empty($data)) {
				wp_send_json(array('token' => '-2'));
			} else if (Pizzakit::outsideTimeFrame()) {
				wp_send_json(array('token' => '-3'));
			} else if (Pizzakit::contains_only_toppings($data['cart'])) {
        wp_send_json(array('token' => '-4'));
		  } else { // else insert the stuff and create payment
				$order = Pizzakit::insert_into_tables($data);
				$response = Pizzakit::create_payment($order);
				if ($response > 0) {
					wp_send_json(array('token' => strval($order[0])));
				} else {
					wp_send_json(array('token' => '-1'));
				}
			}
		}
	}

	// checks if an order only contains toppings
	private static function contains_only_toppings($cart)
	{
		global $wpdb;
		$sql = "SELECT * FROM " . $wpdb->prefix . "items";
		$items = $wpdb->get_results($sql, $output = ARRAY_A);

		foreach ($cart as $cart_item) {
			foreach ($items as $menu_item) {
				if ($menu_item['name'] == $cart_item[0]) {
					if ($menu_item['main_item']) {
						return false;
					}
				}
			}
		}
		return true;
	}

	// returns true if the incoming order has negative quantities or is empty
	private static function is_empty($data)
	{
		$sum = 0;
		foreach ($data["cart"] as $item) {
			$sum = $sum + $item[1];
			if ($item[1] < 0)
				return (true);
		}
		if ($sum <= 0) {
			return true;
		}
		return (false);
	}

	/**
	 * Returns true if the current time and date is outside of the "open" time
	 * frame.
	 */
	public static function outsideTimeFrame() {
		$weekday = date('N');
		$hour = date('G');

		$startWeekday = get_site_option('pizzakit_time_start_weekday');
		if ($weekday < $startWeekday) {
			return true;
		}
		else if ($weekday == $startWeekday) {
			$startHours = get_site_option('pizzakit_time_start_hours');
			if ($hour < $startHours) {
				return true;
			}
		}
		else {
			$endWeekday = get_site_option('pizzakit_time_end_weekday');
			if ($endWeekday < $weekday) {
				return true;
			}
			else if ($endWeekday == $weekday) {
				$endHours = get_site_option('pizzakit_time_end_hours');
				if ($endHours <= $hour) {
					return true;
				}
			}
		}

		return false;
	}

	public static function item_query_handler($data)
	{
		global $wpdb;
		$sql = "SELECT * FROM " . $wpdb->prefix . "items";
		$items = $wpdb->get_results($sql);
		wp_send_json($items);
	}

	public static function payment_query_handler($data)
	{
		# Return payment status of order
		# No side effects
		global $wpdb;
		$table = $wpdb->prefix . 'orders';
		$query = $wpdb->prepare('SELECT status FROM ' . $table . ' WHERE id = %d', $data->get_url_params()['id']);
		$res = $wpdb->get_var($query);
		if ($res != NULL) {
			wp_send_json(array("payment" => $res));
		} else {
			wp_send_json(array("error" => "Invalid orderID"));
		}
	}

	private static function validate_callback($order_id, $uuid)
	{
		global $wpdb;
		$table = $wpdb->prefix . 'orders';
		$query = $wpdb->prepare('SELECT uuid FROM ' . $table . ' WHERE id = %d', $order_id);
		$res = $wpdb->get_var($query);
		trigger_error("Checking: " . $uuid . " === " . $res . " -> " . strcmp($res, $uuid));
		return (strcmp($res, $uuid) == 0);
	}

	public static function swish_callback_handler($_data)
	{
		#todo, validate callback
		#all this does is set status field to Payed for the order_id
		if ($_data->get_url_params()['id']) {
			$order_id = $_data->get_url_params()['id'];
			$req_uuid = $_data->get_json_params()['id'];
			#Is this payment id coupled to our order?
			if (Pizzakit::validate_callback($order_id, $req_uuid)) {
				if ($resp = Pizzakit::verify_swish_payment($req_uuid)) {
					$resp_json = json_decode($resp, true);
					if ($resp_json['status'] == "PAID") {
						global $wpdb;
						$table = $wpdb->prefix . 'orders';
						$res = $wpdb->update($table, array('status' => $resp_json['status']), array('id' => $_data->get_url_params()['id']), array('%s'), array('%d'));
						if (!$res) {
							trigger_error("Pizzakit: error creating entry in orders");
						}
						$query = $wpdb->prepare('SELECT id,location,name,email from '.$table.' WHERE id = %d',$order_id);
						$res = $wpdb->get_results($query);
						trigger_error(print_r($res,$return=true));
						Pizzakit::send_payment_confirmation($res[0]);
					}
				}
			}
		}
	}

	private static function send_payment_confirmation($details){
		$mail = new PHPMailer(true);
		try {
			//Server settings
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
			$mail->isSMTP();                                            // Send using SMTP
			$mail->Host       = 'smtp.server.example';                    // Set the SMTP server to send through
			$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
			$mail->Username   = 'admin';                     // SMTP username
			$mail->Password   = 'hunter2';                               // SMTP password
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
			$mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
		
			//Recipients
			$mail->setFrom('from addr@coolsvile.cs', 'Name');
			$mail->addAddress($details->email, 'Customer');     // Add a recipient
			$mail->addReplyTo('from addr@coolsvile.cs', 'Information');
		
			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = "Menomale pizza kit order ".$details->id;
			$mail->Body    = Pizzakit::generate_mail_body($details->id, $details->name, $details->location);
			$mail->AltBody = "Order ".$details->id." registrerad under namn".$details->name;
		
			$mail->send();
		} catch (Exception $e) {
			trigger_error("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
		}
	}

	private static function generate_mail_body($id, $name, $location) {
		ob_start();
		?>
			<h3>Hej, vi har tagit emot din order!</h3>
			<p>Order <?php $id ?> registrerad under namn <?php $name ?>.</p>
			<p>
				Hämta upp din order från <?php get_site_option('pizzakit_time_pickup_start_day') ?> - <?php get_site_option('pizzakit_time_pickup_end_day') ?> denna vecka på Menomale i <?php $location ?>.
			</p>
		<?php
		return ob_get_clean();
	}

	private static function create_payment($order)
	{
		#Try to create a payment with swish
		# if ok create a payment in db
		$order_id = $order[0];
		$order_total = $order[1];
		$tel_nr = $order[2];

		if ($order_total < 1) {
			global $wpdb;
			$table = $wpdb->prefix . 'orders';
			$data = array('uuid' => '-2', 'status' => 'INVALID_TOTAL');
			$where = array('id' => $order_id);
			$format = array('%s', '%s');
			$where_format = array('%d');
			$wpdb->update($table, $data, $where, $format, $where_format);
			return (-1);
		}

		$res = Pizzakit::create_swish_payment($order_id, $order_total, $tel_nr);

		if ($res['response'] !== NULL) {
			global $wpdb;
			$table = $wpdb->prefix . 'orders';
			$data = array('uuid' => $res['uuid'], 'status' => 'PENDING');
			$where = array('id' => $order_id);
			$format = array('%s', '%s');
			$where_format = array('%d');
			$wpdb->update($table, $data, $where, $format, $where_format);
			return ($order_id);
		}
		return (-1);
	}

	private static function create_swish_payment($order_id, $cost, $tel_nr)
	{
		$tel_nr = Pizzakit::pretty_nr($tel_nr);
		$random_uuid = str_replace("-", "", wp_generate_uuid4());
		$endpoint = "/v2/paymentrequests/" . $random_uuid;
		$method = CURLOPT_PUT;
		$data = array(
			"payeePaymentReference" => $order_id,
			"callbackUrl" => get_home_url() . "/index.php/wp-json/pizzakit/callback/" . $order_id,
			"payerAlias" => $tel_nr,
			"payeeAlias" => get_site_option('pizzakit_swish_number'),
			"amount" => $cost,
			"currency" => "SEK",
			"message" => "Menomale pizzakit"
		);
		return (array('uuid' => $random_uuid, 'response' => Pizzakit::communicate_with_swish($endpoint, $method, $data)));
	}

	private static function pretty_nr($tel_nr)
	{
		if ($tel_nr[0] == '+') {
			return substr($tel_nr, 1);
		}
		if (substr($tel_nr, 0, 2) == '00') {
			return substr($tel_nr, 2);
		}
		if (substr($tel_nr, 0, 2) == '07') {
			return '46' . substr($tel_nr, 1);
		}
		return $tel_nr;
	}

	private static function verify_swish_payment($swish_id)
	{
		$endpoint = "/v1/paymentrequests/" . $swish_id;
		$method = CURLOPT_HTTPGET;
		$data = array();
		return Pizzakit::communicate_with_swish($endpoint, $method, $data);
	}

	private static function communicate_with_swish($endpoint, $method, $data)
	{
		$_url = "https://mss.cpc.getswish.net/swish-cpcapi/api" . $endpoint;
		$_test_uuid = "22826f1c-eda4-4577-b615-ebdb4d9fcb86";

		$ch = curl_init($_url);

		if ($method == CURLOPT_HTTPGET) {
			curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		} elseif (CURLOPT_PUT) {
			$data_string = json_encode($data);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string)
				)
			);
		}
		curl_setopt($ch, CURLOPT_SSLCERTPASSWD, "swish");
		curl_setopt($ch, CURLOPT_SSLCERTTYPE, "PEM");
		curl_setopt($ch, CURLOPT_SSLKEYTYPE, "PEM");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/Swish_TLS_RootCA.pem');
		curl_setopt($ch, CURLOPT_SSLKEY, __DIR__ . '/Swish_Merchant_TestCertificate_1234679304.key');
		curl_setopt($ch, CURLOPT_SSLCERT, __DIR__ . '/Swish_Merchant_TestCertificate_1234679304.pem');

		curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		curl_setopt(
			$ch,
			CURLOPT_HEADERFUNCTION,
			function ($curl, $header) use (&$headers) {
				// this function is called by curl for each header received
				$len = strlen($header);
				$header = explode(':', $header, 2);
				if (count($header) < 2) {
					// ignore invalid headers
					return $len;
				}

				return $len;
			}
		);

		$result = curl_exec($ch);

		$info = curl_getinfo($ch);
		if ($result === FALSE) {
			trigger_error(curl_error($ch));
			return (NULL);
		}
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($result, 0, $header_size);
		$body = substr($result, $header_size);
		curl_close($ch);
		return ($body);
	}


	private static function insert_into_tables($_data)
	{

		global $wpdb;
		$sql = "SELECT * FROM " . $wpdb->prefix . "items";
		$items = $wpdb->get_results($sql, $output = ARRAY_N);

		//insert into orders, using insert() function to get it prepared. Returns id of last inserted order.
		$_table = $wpdb->prefix . 'orders';
		$_dataArr = array(
			'id' => null, 'location' => Pizzakit::sanitizeText($_data["location"]), 'email' => Pizzakit::sanitizeText($_data["email"]), 'telNr' => Pizzakit::sanitizeText($_data["telNr"]),
			'name' => Pizzakit::sanitizeText($_data["name"]), 'comments' => Pizzakit::sanitizeText($_data["comments"])
		);
		$_format = array('%d', '%s', '%s', '%s', '%s', '%s');
		$wpdb->insert($_table, $_dataArr, $_format);
		$_lastid = $wpdb->insert_id;

		$total_cost = 0;
		$item_cost = 0;

		//insert into entries
		foreach ($_data["cart"] as $_item) {
			if($_item[1] == 0){
				continue;
			}
			foreach ($items as $i) {
				if ($i[0] == $_item[0]) {
					$total_cost += $i[2] * $_item[1];
				}
			}
			$_table = $wpdb->prefix . 'entries';
			$_dataArr = array('orderID' => $_lastid, 'item' => $_item[0], 'quantity' => $_item[1]);
			$_format = array('%d', '%s', '%d');
			$wpdb->insert($_table, $_dataArr, $_format);
		}
		return (array($_lastid, $total_cost, $_data['telNr']));
	}

	/**
	 * Removes angle brackets from strings so that they safely can be inserted
	 * in HTML outputs.
	 * 
	 * @param string $text Text that might contain HTML-tags.
	 * 
	 * @return string Text that won't break HTML.
	 */
	private static function sanitizeText($text)
	{
		$text = str_replace("<", "&lt;", $text);
		$text = str_replace(">", "&gt;", $text);
		return $text;
	}

	public static function fill_menu($data)
	{
		global $wpdb;
		$table = $wpdb->prefix . 'items';

		//insert items
		foreach ($data["menu"] as $item) {
			$data_arr = array('name' => $item["name"], 'list_order' => $item["list_order"], 'price' => $item["price"], "comment" => $item["comment"], "main_item" => $item["main_item"]);
			$format = array('%s', '%d', '%d', '%s', '%d');
			$wpdb->insert($table, $data_arr, $format);
		}
	}
}
