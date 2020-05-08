import React from 'react';

class ConSuccess extends React.Component {
	
	render() {
		return(
			<div className="confirm-page">
				<h2>Tack!</h2>
				<p>Din order har nu placerats, leverans kommer att ske innan 16:00 kommande arbetsdag!</p>
				<p>Information om din beställning samt kvitto har även skickats ut via mail.</p>
			</div>
		);
	}
}

export default ConSuccess;