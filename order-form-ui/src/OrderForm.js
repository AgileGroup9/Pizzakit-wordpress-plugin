import React from 'react';
import Small_item from './Items';
import PaymentConfirmation from './PaymentConfirmation';
import ConSuccess from './ConSuccess'
import ConFailed from './ConFailed'

// Main Application
// Renders a form and keeps track of items the client has selected
class OrderForm extends React.Component {
	constructor(props){
		super(props);

		// Hard coded list of items
		// TODO: retrive items and price from server-side api
		this.post_address = props.post_address;
		this.state = {};
		this.is_email_valid = true;
		this.is_telNr_valid = true;
		this.is_postalCode_valid = true;

		// Required for intercepting onChange events from <input>
		this.handle_detail_update = this.handle_detail_update.bind(this);
		// Use list_items to create a key-map for tracking the users shopping cart
		this.items = [];
		this.sum = 0;
		this.prices = new Map();
		this.state = {
			cart : new Map(),
			email : '',
			name : '',
			telNr : '',
			address : '',
			doorCode : '',
			postalCode : '',
			comments : '',
		};
		this.get_items();
	}

	handle_cart_update(item,delta){
		const newValue = this.state.cart.get(item) + delta;
		if(newValue >= 0){
			this.setState({
				cart : this.state.cart.set(item,newValue),
				delivery_method : this.state.delivery_method,
			});
		}
	}

	handle_detail_update(event) {
		const target = event.target;
		const name = target.name;
		const value = target.value;
		this.setState({
			[name]: value
		});
		this.validate(name,value);
	}

	is_fields_empty(){
		const current_state = {... this.state};
		for (const property in current_state){
			if(property != 'cart' && property != 'comments' && property != 'doorCode' && current_state[property] === ''){
				return true;
			}
		}
		return false;
	}

	async get_items(){
		const response = await fetch('/index.php/wp-json/pizzakit/items');
		this.items = await response.json();
		this.prices = new Map(this.items.map(x => [x['name'],x['price']]));
		console.log(this.prices);
		this.setState({
			cart : new Map( this.items.map(x => [x['name'],0])),
			email : '',
			name : '',
			telNr : '',
			address : '',
			doorCode : '',
			postalCode : '',
			comments : '',
		});
	}

	async handle_submit(target_addr) {
		if(this.is_fields_empty()){
			alert('Var snäll och fyll i alla obligatoriska fält');
			return;
		}
		const validation_results = this.check_validation();
		if(validation_results !== ''){
			alert(validation_results);
		}
		const response = await fetch(target_addr, {
			method: 'POST',
			mode: 'no-cors', 
			headers: {
				'Access-Control-Allow-Origin':'true',
				'Content-Type': 'application/json'
			},
			body: this.state_to_json(),
		});
		if (response.ok) {
			const json = await response.json();
			
			if (json.token != null && json.token !== '-1') {
				this.props.navigateTo(PaymentConfirmation, { token: json.token });
			}
			else {
				this.props.navigateTo(ConFailed);
			}
		}
		else {
			this.props.navigateTo(ConFailed);
		}
	}

	validate_email(str){
		var re = /^[a-ö\-.]+@[a-ö]+\.[a-ö]+$/;
		return re.exec(str) !== null;	
	}

	validate_tel(str){
		var re = /^[0-9]{8,15}$/;
		return re.exec(str.replace(/\s/g,'')) !== null;
	}

	validate_postalcode(str){
		var re = /^[0-9]{5}$/;
		return re.exec(str.replace(/\s/g,'')) !== null;
	}

	validate(name,value){
		switch (name) {
		case 'email':
			this.is_email_valid = this.validate_email(value);
			console.log('Validating Email: '+ this.is_email_valid);
			break;
		case 'telNr':
			this.is_telNr_valid = this.validate_tel(value);
			console.log('Validating Tel: '+ this.is_telNr_valid);
			break;
		case 'postalCode':
			this.is_postalCode_valid = this.validate_postalcode(value);
		}
	}

	check_validation(){
		// Generates a string of (if any) validation errors that exist
		const email_error = 'Ogiltig email address';
		const tel_error = 'Ogiltig telefonnummer';
		const postalCode_error = 'Ogiltig Postnummer';
		var res = '';
		res = this.is_email_valid ? res : res + email_error + '\n';
		res = this.is_telNr_valid ? res : res + tel_error + '\n';
		res = this.is_postalCode_valid ? res : res + postalCode_error + '\n';
		return res;

	}

	state_to_json(){
		const cart = Array.from(this.state.cart.entries());
		var json_obj = Object.assign({},this.state);
		json_obj.cart = cart;
		json_obj.pizzakitFormSubmission = true;
		return JSON.stringify(json_obj);
	}

	render() {
		// Render toppings dynamicaly
		const extras = this.items.filter(x => x["main_item"] === false);
		const extra_list = extras.map(x => {
			return(<Small_item
				key = {x['name']}
				name={x['name']}
				desc={x['comment']}
				price={this.prices.get(x['name'])}
				count={this.state.cart.get(x['name'])}
				onClick={(name,delta) => this.handle_cart_update(name,delta)}
			/>);
		});

		const mains = this.items.filter(x => x["main_item"] === true);
		const main_list = mains.map(x => {
			return(<Small_item 
				key = {x['name']}
				name={x['name']}
				desc={x['comment']}
				price={this.prices.get(x['name'])}
				count={this.state.cart.get(x['name'])}
				onClick={(name,delta) => this.handle_cart_update(name,delta)}
			/>);
		});

		this.sum = 0;
		this.state.cart.forEach((v,k,m) => this.sum += this.prices.get(k)*v);
		// Renders form. For info about how to add stuff, google jsx
		// TODO: remove inline css (code smell)
		return(
			<p>
				<div className="form-group">
					<h6>Storlek på pizzakit:</h6>
					<div>
					{/*Main items are rendered here*/ main_list}
					</div>
					<div>
						<small className="form-text text-muted"> I alla pizzakit ingår Tomatsås, San Marzano Fior di Latte
							(mozarella) samt en basilikakruka</small>
					</div>
				</div>
				<hr/>

				<div className="form-group">
					<h6>Välj toppings:</h6>
					{/*Extras are rendered here*/ extra_list}
				</div>
				<hr/>

				<h6><strong>Totalkostnad:</strong> {this.sum}kr</h6>
				<p>(obligatoriska fält: <span>*</span>)</p>
				<div id="detail-form">
					<div className="form-group" id="email">
						<label htmlFor="email_inpt">Email<span>*</span>:</label>
						<input type="email" name="email" id="email_inpt" onChange={this.handle_detail_update} className={'form-control ' + (this.is_email_valid ? '' : 'invalid')} placeholder="exempel@mail.se" pattern="[a-ö\-\.]+@[a-ö]+\.[a-ö]+"/>
					</div>
					<div className="form-group" id="tele">
						<label htmlFor="tel_inpt">Telefonnummer<span>*</span>:</label>
						<input type="tel" name="telNr" id="tel_inpt" onChange={this.handle_detail_update} className={'form-control ' + (this.is_telNr_valid ? '' : 'invalid')} aria-describedby="emailHelp" placeholder="070......."/>
					</div>
					<div className="form-group">
						<label htmlFor="name_inpt">Namn<span>*</span>:</label>
						<input type="text" name="name" id="name_inpt" onChange={this.handle_detail_update} className="form-control" aria-describedby="emailHelp" placeholder="Glen Glensson"/>
					</div>
					<div className="form-group" >
						<label htmlFor="addr_inpt">Leveransaddress<span>*</span>:</label>
						<input type="text" name="address" id="addr_inpt" onChange={this.handle_detail_update} className="form-control" aria-describedby="emailHelp" placeholder="Pizzagatan 123"/>
					</div>
					<div className="form-group" >
						<label htmlFor="post_nr_inpt">Postkod<span>*</span>:</label>
						<input type="text" name="postalCode" id="post_nr_inpt" onChange={this.handle_detail_update} className={'form-control ' + (this.is_postalCode_valid ? '' : 'invalid')} aria-describedby="emailHelp" placeholder="123 45"/>
					</div>
					<div className="form-group" >
						<label htmlFor="code_inpt">Portkod:</label>
						<input type="text" name="doorCode" id="code_inpt" onChange={this.handle_detail_update} className="form-control" aria-describedby="emailHelp" placeholder="0001"/>
					</div>
				</div>
				<hr/>
				<div id="final-form">
					<textarea name="comments" rows="2" cols="30" placeholder="Kommentarer" onChange={this.handle_detail_update}></textarea>
					<button onClick={() => this.handle_submit(this.post_address)} className="btn btn-primary">Gå till betalning</button>
				</div>
			</p>
		);
	}
}

export default OrderForm;