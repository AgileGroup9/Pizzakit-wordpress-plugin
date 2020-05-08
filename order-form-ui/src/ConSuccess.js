import React from 'react';

class ConSuccess extends React.Component {
	
	render() {
		return(
			<div className="confirm-page">
				<h2>Tack!</h2>
				<p>Betalning är nu mottagen och din order har placerats, leverans kommer att ske innan 16:00 kommande arbetsdag!</p>
				<p>Information om din beställning samt kvitto har skickats ut via mail.</p>
			</div>
		);
	}
}

export default ConSuccess;