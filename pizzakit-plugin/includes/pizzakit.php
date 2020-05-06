<?php

class Pizzakit {
	public static $resp = "None";

	public static function run() {
		add_action("init", "Pizzakit::init");
		add_action( 'rest_api_init', function () {
			register_rest_route( 'pizzakit', '/callback/(?P<id>\d+)', array(
			  'methods' => 'POST',
			  'callback' => [__CLASS__, 'swish_callback_handler'],
			) );
			register_rest_route( 'pizzakit', '/payment/(?P<id>\d+)', array(
				'methods' => 'GET',
				'callback' => [__CLASS__, 'payment_query_handler'],
			  ) );
		  } );
	}

	public static function init() {
		Pizzakit::handle_post();

		require_once plugin_dir_path( __FILE__ ) . 'pizzakit-blocks.php';
		Pizzakit_Blocks::register_blocks();
	}

	public static function handle_post() {

		$json = file_get_contents("php://input");
		$data = json_decode($json, true);

		// Create a "secret" JSON field that's only set when posting from our
		// form so that we don't respond to other post requests.
		// In this the example it's "pizzakitFormSubmission".
		if (isset($data["pizzakitFormSubmission"])) {

			$order_id = Pizzakit::insert_into_tables($data);
			$response = Pizzakit::create_payment($order_id);

			if($response != -1){
				wp_send_json(array( 'token' => $order_id));
			}
			else{
				wp_send_json(array('token' => -1));
			}
		}

		if (isset($data["refresh_menu_items"])){

			Pizzakit::refresh_menu_items();

			$response = array('menu_items_refreshed' => true);
			wp_send_json($response);
		}
	}

	public static function payment_query_handler($data)
	{
		# Return payment status of order
		# No side effects
		global $wpdb;
		$table = $wpdb->prefix . 'payment';
		$query = $wpdb->prepare('SELECT status FROM '.$table.' WHERE orderID = %d',$data['id']);
		$res = $wpdb->get_var($query);
		if($res != NULL){
			wp_send_json(array("payment" => $res));
		}
		else{
			wp_send_json(array("error" => "Invalid orderID"));
		}
	}

	public static function swish_callback_handler($_data){
		#todo, validate callback
		#all this does is set status field to Payed for the order_id
		trigger_error(print_r($_data,$return=true));
		trigger_error(print_r($_data['params']['JSON']['status'],$return=true));
		trigger_error(print_r($_data['params']['URL']['id'],$return=true));
		if($_data->get_url_params()['id']){
			global $wpdb;
			$table = $wpdb->prefix . 'payment';
			$res = $wpdb->update($table,array( 'status' => $_data->get_json_params()['status']),array('orderID' => $_data->get_url_params()['id']),array('%s'),array('%d'));
			if($res == false){
				var_dump(false);
			}
		}
	}

	private static function create_payment($order_id){
		#Try to create a payment with swish
		# if ok create a payment in db
		if(Pizzakit::create_swish_payment($order_id,0.02)){
			global $wpdb;
			$table = $wpdb->prefix . 'payment';
			$data = array('orderID' => $order_id,'status'=>'PENDING');
			$format = array('%d','%s');
			$wpdb->insert($table,$data,$format);
			return($order_id);
		}
		return(-1);
	}

	private static function create_swish_payment($order_id,$cost){
		$_url = "https://mss.cpc.getswish.net/swish-cpcapi/api/v1/paymentrequests/";
		$_test_uuid = "22826f1c-eda4-4577-b615-ebdb4d9fcb86";

		$ch = curl_init($_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_CAINFO, __DIR__.'/Swish_TLS_RootCA.pem');
		curl_setopt($ch, CURLOPT_SSLCERT, __DIR__.'/Swish_Merchant_TestCertificate_1234679304.pem');
		curl_setopt($ch, CURLOPT_SSLKEY, __DIR__.'/Swish_Merchant_TestCertificate_1234679304.key');

		curl_setopt($ch, CURLOPT_HEADERFUNCTION,
				function($curl, $header) use (&$headers) {
				// this function is called by curl for each header received
				$len = strlen($header);
				$header = explode(':', $header, 2);
				if (count($header) < 2) {
					// ignore invalid headers
						return $len;
				} 

				$name = strtolower(trim($header[0]));
				echo "[". $name . "] => " . $header[1];

				return $len;
				}
		);

		$data = array("payeePaymentReference" => "0123456789", "callbackUrl" => "https://morse.se/index.php/wp-json/pizzakit/callback/" . $order_id, "payerAlias" => "4671234768", "payeeAlias" => "1234679304", "amount" => "1", "currency" => "SEK", "message" => "Where is the money?");          
		$data_string = json_encode($data);
																
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);    
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
			'Content-Type: application/json',                                                                                
			'Content-Length: ' . strlen($data_string))                                                                       
		);                                                                                                                   

		if(!$response = curl_exec($ch)) { 
			trigger_error(curl_error($ch));
			return(false);
		}
		curl_close($ch);
		var_dump($response);
		return(true);
	}

	private static function insert_into_tables($_data){

		global $wpdb;

		//insert into orders, using insert() function to get it prepared
		$_table = $wpdb->prefix. 'orders';
		$_dataArr = array('id' => null,'email' => $_data["email"],'name' => $_data["name"],'telNr' => $_data["telNr"],
			'address' => $_data["address"], 'doorCode' => $_data["doorCode"], 'postalCode' => $_data["postalCode"], 'comments' => $_data["comments"]);
		$_format = array('%d','%s','%s','%s','%s','%s','%s','%s');
		$wpdb->insert($_table,$_dataArr,$_format);
		$_lastid = $wpdb->insert_id;

		//insert into entries
		foreach ($_data["cart"] as $_item){
			$_table = $wpdb->prefix. 'entries';
			$_dataArr = array('orderID' => $_lastid,'item'=>$_item[0],'quantity'=>$_item[1]);
			$_format = array('%d','%s','%d');
			$wpdb->insert($_table,$_dataArr,$_format);
		}
		return($_lastid);
	}

	public static function refresh_menu_items(){
		global $wpdb;
		$table = $wpdb->prefix . 'items';
		$json = file_get_contents(plugin_dir_path(__FILE__) . 'items_for_sale.json');
		$data = json_decode($json, true);
		
		//drop all data in items-table
		$wpdb->query('TRUNCATE TABLE ' . $table);
		
		//insert items
		foreach ($data["main_items"] as $item){
			$data_arr = array('name' => $item["name"], 'price' => $item["price"], "comment" => $item["comment"], "main_item" => true);
			$format = array('%s','%d','%s','%d');
			$wpdb->insert($table,$data_arr,$format);
		}
		foreach ($data["extras"] as $extra) {
			$data_arr = array('name' => $extra["name"],'price' => $extra["price"],"comment" => $extra["comment"], "main_item" => false);
			$format = array('%s','%d','%s','%d');
			$wpdb->insert($table,$data_arr,$format);
		}
	}
}

?>
