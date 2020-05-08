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

			$order = Pizzakit::insert_into_tables($data);
			$response = Pizzakit::create_payment($order);

			if($response != -1){
				wp_send_json(array( 'token' => $order[0]));
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
		$query = $wpdb->prepare('SELECT status FROM '.$table.' WHERE orderID = %d',$data->get_url_params()['id']);
		$res = $wpdb->get_var($query);
		if($res != NULL){
			wp_send_json(array("payment" => $res));
		}
		else{
			wp_send_json(array("error" => "Invalid orderID"));
		}
	}

	private static function validate_callback($order_id,$uuid){
		global $wpdb;
		$table = $wpdb->prefix . 'payment';
		$query = $wpdb->prepare('SELECT uuid FROM '.$table.' WHERE orderID = %d',$order_id);
		$res = $wpdb->get_var($query);
		trigger_error("Checking: ".$uuid." === ".$res." -> ".strcmp($res,$uuid));
		return(strcmp($res,$uuid) == 0);
	}

	public static function swish_callback_handler($_data){
		#todo, validate callback
		#all this does is set status field to Payed for the order_id
		if($_data->get_url_params()['id']){
			$order_id = $_data->get_url_params()['id'];
			$req_uuid = $_data->get_json_params()['id'];
			#Is this payment id coupled to our order?
			if(Pizzakit::validate_callback($order_id,$req_uuid))
			{
				if($resp = Pizzakit::verify_swish_payment($req_uuid)){
					$resp_json = json_decode($resp,true);
					if($resp_json['status'] == "PAID"){
						global $wpdb;
						$table = $wpdb->prefix . 'payment';
						$res = $wpdb->update($table,array( 'status' => $resp_json['status']),array('orderID' => $_data->get_url_params()['id']),array('%s'),array('%d'));
						if($res == false){
							var_dump(false);
						}
					}
				}
			}
		}
	}

	private static function create_payment($order){
		#Try to create a payment with swish
		# if ok create a payment in db
		$order_id = $order[0];
		$order_total = $order[1];

		$res = Pizzakit::create_swish_payment($order_id,$order_total);
		
		if($res['response'] !== NULL){
			global $wpdb;
			$table = $wpdb->prefix . 'payment';
			$data = array('orderID' => $order_id,'uuid' => $res['uuid'],'status'=>'PENDING');
			$format = array('%d','%s','%s');
			$wpdb->insert($table,$data,$format);
			return($order_id);
		}
		return(-1);
	}

	private static function create_swish_payment($order_id,$cost){
		$random_uuid = str_replace("-","",wp_generate_uuid4());
		$endpoint = "/v2/paymentrequests/".$random_uuid;
		$method = CURLOPT_PUT;
		$data = array(
			"payeePaymentReference" => "0123456789",
			"callbackUrl" => get_home_url() . "/index.php/wp-json/pizzakit/callback/" . $order_id,
			"payerAlias" => "4671234768",
			"payeeAlias" => "1234679304",
			"amount" => $cost,
			"currency" => "SEK",
			"message" => "Menomale pizzakit"
		);          
		return(array('uuid' => $random_uuid, 'response' => Pizzakit::communicate_with_swish($endpoint,$method,$data)));
	}

	private static function verify_swish_payment($swish_id){
		$endpoint = "/v1/paymentrequests/".$swish_id;
		$method = CURLOPT_HTTPGET;
		$data = array();
		return Pizzakit::communicate_with_swish($endpoint,$method,$data);
	}

	private static function communicate_with_swish($endpoint,$method,$data){
		$_url = "https://mss.cpc.getswish.net/swish-cpcapi/api".$endpoint;
		$_test_uuid = "22826f1c-eda4-4577-b615-ebdb4d9fcb86";

		$ch = curl_init($_url);

		if($method == CURLOPT_HTTPGET){
			curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		}
		elseif (CURLOPT_PUT) {
			$data_string = json_encode($data);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
				'Content-Type: application/json',                                                                                
				'Content-Length: ' . strlen($data_string))                                                                       
			);    
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_CAINFO, __DIR__.'/Swish_TLS_RootCA.pem');
		curl_setopt($ch, CURLOPT_SSLCERT, __DIR__.'/Swish_Merchant_TestCertificate_1234679304.pem');
		curl_setopt($ch, CURLOPT_SSLKEY, __DIR__.'/Swish_Merchant_TestCertificate_1234679304.key');

		curl_setopt($ch, CURLINFO_HEADER_OUT, true);

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
		
		$result = curl_exec ($ch);

		$info = curl_getinfo($ch);
		if($result === FALSE) {
			trigger_error(curl_error($ch)); 
			return(NULL);
		}
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($result, 0, $header_size);
		$body = substr($result, $header_size);
		curl_close($ch);
		return($body);
	}

	private static function insert_into_tables($_data){

		$json = file_get_contents(plugin_dir_path(__FILE__) . 'items_for_sale.json');
		$decoded = json_decode($json, true);
		$items = array_merge($decoded['main_items'],$decoded['extras']);

		global $wpdb;

		//insert into orders, using insert() function to get it prepared
		$_table = $wpdb->prefix. 'orders';
		$_dataArr = array('id' => null,'email' => $_data["email"],'name' => $_data["name"],'telNr' => $_data["telNr"],
			'address' => $_data["address"], 'doorCode' => $_data["doorCode"], 'postalCode' => $_data["postalCode"], 'comments' => $_data["comments"]);
		$_format = array('%d','%s','%s','%s','%s','%s','%s','%s');
		$wpdb->insert($_table,$_dataArr,$_format);
		$_lastid = $wpdb->insert_id;

		$total_cost = 0;
		$item_cost = 0;

		//insert into entries
		foreach ($_data["cart"] as $_item){
			foreach($items as $i){
				if($i['name'] == $_item[0]){
					$total_cost += $i['price']*$_item[1];
				}
			}
			$_table = $wpdb->prefix. 'entries';
			$_dataArr = array('orderID' => $_lastid,'item'=>$_item[0],'quantity'=>$_item[1]);
			$_format = array('%d','%s','%d');
			$wpdb->insert($_table,$_dataArr,$_format);
		}
		return(array($_lastid,$total_cost));
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
