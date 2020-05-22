import React from 'react';
import Small_item from './Items';
import PaymentConfirmation from './PaymentConfirmation';
import ConSuccess from './ConSuccess';
import ConFailed from './ConFailed';
import Policy from './Policy';

// Main Application
// Renders a form and keeps track of items the client has selected
class OrderForm extends React.Component {
	constructor(props){
		super(props);

		this.post_address = props.post_address;
		this.state = {};
		this.is_email_valid = true;
		this.is_telNr_valid = true;

		// Required for intercepting onChange events from <input>
		this.handle_detail_update = this.handle_detail_update.bind(this);

		this.items = window.pizzakitItems.sort((a, b) => a.list_order - b.list_order);
		this.prices = new Map(this.items.map(x => [x['name'],x['price']]));
		this.state = props.initState || {
			cart : new Map( this.items.map(x => [x['name'],0])),
			location : '',
			email : '',
			telNr : '',
			name : '',
			comments : '',
			isLoading: false
		};
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
			if(property != 'cart' && property != 'comments' && current_state[property] === ''){
				return true;
			}
		}
		return false;
	}

	async handle_submit(target_addr) {
		if(this.is_fields_empty()){
			alert('Vänligen fyll i alla obligatoriska fält');
			return;
		}
		if(!document.getElementById("policy").checked) {
			alert('Vänligen godkänn köpvillkoren')
			return;
		}
		const validation_results = this.check_validation();
		if(validation_results !== ''){
			alert(validation_results);
		}
		if (!window.confirm('Gå vidare till betalning?')) {
			return;
		}
		this.setState({ isLoading: true });
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
			
			if (json.token != null && parseInt(json.token) > 0) {
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
		}
	}

	check_validation(){
		// Generates a string of (if any) validation errors that exist
		const email_error = 'Ogiltig email address';
		const tel_error = 'Ogiltig telefonnummer';
		var res = '';
		res = this.is_email_valid ? res : res + email_error + '\n';
		res = this.is_telNr_valid ? res : res + tel_error + '\n';
		return res;

	}

	state_to_json(){
		const cart = Array.from(this.state.cart.entries());
		var json_obj = Object.assign({},this.state);
		json_obj.cart = cart;
		json_obj.pizzakitFormSubmission = true;
		return JSON.stringify(json_obj);
	}

	/**
	 * Returns true if the current time and date is outside of the "open" time
	 * frame.
	 */
	outsideTimeFrame() {
		const date = new Date();
		const weekday = date.getDay() || 7; // JavaScript days are Sun-Sat 0-6 but we want Mon-Sun 1-7.
		const hour = date.getHours();

		if (weekday < window.pizzakitTimes.start.weekday) {
			return true;
		}
		else if (weekday == window.pizzakitTimes.start.weekday) {
			if (hour < window.pizzakitTimes.start.hours) {
				return true;
			}
		}
		else {
			if (window.pizzakitTimes.end.weekday < weekday) {
				return true;
			}
			else if (window.pizzakitTimes.end.weekday == weekday) {
				if (window.pizzakitTimes.end.hours <= hour) {
					return true;
				}
			}
		}

		return false;
	}

	show_policy() {
		this.props.navigateTo(Policy, { state: this.state, post_address: this.post_address });
	}

	form() {
		// Render items dynamicaly
		const extras = this.items.filter(x => x["main_item"] == false);
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

		const mains = this.items.filter(x => x["main_item"] == true);
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

		var sum = 0;
		this.state.cart.forEach((v,k,m) => sum += this.prices.get(k)*v);
		// Renders form. For info about how to add stuff, google jsx
		// TODO: remove inline css (code smell)
		return(
			<p className={`${this.state.isLoading ? 'loading' : ''}`}>
				<div className="form-group">
					<h6>Storlek på pizzakit:</h6>
					<div>
					{/*Main items are rendered here*/ main_list}
					</div>
					<div>
						<small className="form-text text-muted">
							I alla pizzakit ingår tomatsås San Marzano, fior di latte (mozzarella), en basilikakruka, samt instruktioner
						</small>
					</div>
				</div>
				<hr/>

				<div className="form-group">

				<h6>Välj extra tillägg:</h6>
				{/*Extras are rendered here*/ extra_list}
					
				</div>
				<hr/>

				<h6><strong>Totalkostnad:</strong> {sum}kr</h6>
				<p>(obligatoriska fält: <span>*</span>)</p>
				<div id="detail-form">
					<div className="form-group" id="email">
						<label htmlFor="email_inpt">Email<span>*</span>:</label>
						<input type="email" name="email" id="email_inpt" onChange={this.handle_detail_update} className={'form-control ' + (this.is_email_valid ? '' : 'invalid')} placeholder="exempel@mail.se" pattern="[a-ö\-\.]+@[a-ö]+\.[a-ö]+" value={this.state.email}/>
					</div>
					<div className="form-group" id="tele">
						<label htmlFor="tel_inpt">Telefonnummer<span>*</span>:</label>
						<input type="tel" name="telNr" id="tel_inpt" onChange={this.handle_detail_update} className={'form-control ' + (this.is_telNr_valid ? '' : 'invalid')} aria-describedby="emailHelp" placeholder="07........" value={this.state.telNr}/>
					</div>
					<div className="form-group">
						<label htmlFor="name_inpt">Namn<span>*</span>:</label>
						<input type="text" name="name" id="name_inpt" onChange={this.handle_detail_update} className="form-control" aria-describedby="emailHelp" placeholder="Namn Efternamn" value={this.state.name}/>
					</div>
					<div className="form-group" >
						<label htmlFor="pickup_inpt">Uthämtningsställe<span>*</span>:</label>
						<select name="location" id="pickup_inpt" onChange={this.handle_detail_update}>
							<option value="" disabled selected={this.state.location == ''}>Välj:</option>
							<option value="Vasastan" selected={this.state.location == 'Vasastan'}>Vasastan</option>
							<option value="Kungsholmen" selected={this.state.location == 'Kungsholmen'}>Kungsholmen</option>
							<option value="Östermalm" selected={this.state.location == 'Östermalm'}>Östermalm</option>
						</select>
					</div>
					<div className="form-group">
						<input type="checkbox" id="policy" name="policy" value="TRUE"></input>
						<label htmlFor="policy">Jag godkänner <a onClick={() => this.show_policy()}>köpvillkoren</a><span>*</span>:</label>
					</div>
				</div>
				<hr/>
				<div id="final-form">
					<textarea name="comments" rows="2" cols="30" placeholder="Kommentarer" onChange={this.handle_detail_update}></textarea>
					<button onClick={() => this.handle_submit(this.post_address)} className="btn btn-primary">
						<span>Gå till betalning</span>
						<div className="spinner"></div>
					</button>
				</div>
			</p>
		);
	}

	render() {
		const weekdays = [ 'måndag', 'tisdag', 'onsdag', 'torsdag', 'fredag', 'lördag', 'söndag' ];
		const startText = weekdays[window.pizzakitTimes.start.weekday - 1] + (window.pizzakitTimes.start.hours != 0 ? ` ${window.pizzakitTimes.start.hours}:00` : '');
		const endText = weekdays[window.pizzakitTimes.end.weekday - 1] + (window.pizzakitTimes.end.hours != 24 ? ` ${window.pizzakitTimes.end.hours}:00` : '');
		const pickupText = weekdays[window.pizzakitTimes.pickup.startDay - 1] + (window.pizzakitTimes.pickup.startDay != window.pizzakitTimes.pickup.endDay ? ' till ' + weekdays[window.pizzakitTimes.pickup.endDay - 1] : '');

		let content;
		if (this.outsideTimeFrame()) {
			content = (
				<p className="has-text-align-center">
					<div>
						<h6>Vi tar inte emot några beställningar just nu</h6>
					</div>
				</p>
			);
		}
		else {
			content = this.form();
		}

		return (
			<>
				<p className="has-text-align-center">Vi tar emot beställningar mellan {startText} och {endText} för upphämtning på {pickupText}!</p>
				{content}
			</>
		);
	}
}

export default OrderForm;
