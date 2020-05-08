import React from 'react';

class PaymentConfirmation extends React.Component {
	constructor(props) {
		super(props);

		this.pollFailures = 0;
		this.timeout = null;
		this.controller = new AbortController();

		this.state = {
			loading: true,
			message: 'Vi har mottagit din order, och väntar nu på din betalning.'
		};
	}

	componentDidMount() {
		this.fetchUpdate();

		window.location = 'swish://paymentrequest?token=' + this.props.token;
	}

	componentWillUnmount() {
		this.controller.abort();
		clearTimeout(this.timeout);
	}

	async fetchUpdate() {
		try {
			const response = await fetch(
				`//${location.host}/index.php/wp-json/pizzakit/payment/${this.props.token}`,
				{
					signal: this.controller.signal,
					method: 'GET'
				}
			);
			const json = await response.json();
			if (json.error != null) {
				throw new Error(json.error);
			}
			else if (json.payment != null && json.payment !== 'PENDING') {
				if (json.payment === 'PAYED') {
					// Dummy page for now
					this.props.navigateTo(<p>Din order är laggd och betald.</p>);
	
					this.setState({
						loading: false,
						message: 'Betalning mottagen. Du skickas nu vidare till din orderbekräftelse.'
					});
	
					return; // Stop polling
				}
				else {
					throw new Error(`Payment status: ${json.payment}`);
				}
			}

			this.pollFailures = 0;
		}
		catch (ex) {
			this.pollFailures++;

			if (this.pollFailures > PaymentConfirmation.acceptableFailures) {
				const errorMessage = 'Något gick fel: ' + ((typeof ex === 'object' ? ex.message : ex) || '...men vi har ingen aning om vad 😢');
				this.setState({
					loading: false,
					message: errorMessage
				});

				alert(errorMessage);
				
				return; // Stop polling
			}
		}

		this.timeout = setTimeout(this.fetchUpdate.bind(this), PaymentConfirmation.pollInterval);
	}

	render() {
		return(
			<p className="payment-confirmation">
				<figure><div/></figure>
				<div className={`progress has-accent-background-color ${this.state.loading ? '' : 'hidden'}`}/>
				<div>
					<p>{this.state.message}</p>
				</div>
			</p>
		);
	}
}

PaymentConfirmation.pollInterval = 500;
PaymentConfirmation.acceptableFailures = 5;

export default PaymentConfirmation;