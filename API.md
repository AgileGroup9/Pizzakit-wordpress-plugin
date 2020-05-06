$URL = hostname for site

make order:
	POST { order data, 'pizzakitFormSubmission' : true} to $URL => on succsess JSON {"token":"nummber}
																   on failiure JSON {"token":"-1"}

check_payment:
	GET $URL/index.php/wp-json/pizzakit/payment/{token} => on succsess JSON {"payment":"PENDING|PAYED|CANCELED|REFUNDED"}
														   on failiure JSON {"error":"Invalid orderID"}