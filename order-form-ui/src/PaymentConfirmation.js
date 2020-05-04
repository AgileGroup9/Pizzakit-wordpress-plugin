import React from 'react';

class PaymentConfirmation extends React.Component {
	constructor(props) {
		super(props);

		this.pollFailures = 0;
		this.timeout = null;
		this.controller = new AbortController();
	}

	componentDidMount() {
		this.fetchUpdate();
	}

	componentWillUnmount() {
		this.controller.abort();
		clearTimeout(this.timeout);
	}

	async fetchUpdate() {
		try {
			const response = await fetch(
				this.props.post_address,
				{
					signal: this.controller.signal,
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({ 'pizzakitSwishConfirmation': true })
				}
			);
			const json = await response.json();
			// Dummy check for now
			if (json.success === true) {
				// Dummy page for now
				this.props.navigateTo(<p>Din order är laggd och betald.</p>);
			}

			this.pollFailures = 0;
		}
		catch (ex) {
			this.pollFailures++;

			if (this.pollFailures > PaymentConfirmation.acceptableFailures) {
				alert(
					'Något gick fel: ' +
					((typeof ex === 'object' ? ex.message : ex) || '...men vi har ingen aning om vad 😢')
				);
			}
		}

		this.timeout = setTimeout(this.fetchUpdate.bind(this), PaymentConfirmation.pollInterval);
	}

	render() {
		return(
			<p className="payment-confirmation">
				<figure><div/></figure>
				<progress/>
				<div>
					<p>Vi har mottagit din order, och väntar nu på din betalning.</p>
				</div>
			</p>
		);
	}
}

PaymentConfirmation.pollInterval = 500;
PaymentConfirmation.acceptableFailures = 5;

export default PaymentConfirmation;