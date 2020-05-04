import React from 'react';

class PaymentConfirmation extends React.Component {
	constructor(props) {
		super(props);
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

export default PaymentConfirmation;