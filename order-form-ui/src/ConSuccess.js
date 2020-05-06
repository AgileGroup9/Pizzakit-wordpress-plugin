import React from 'react';

class ConSuccess extends React.Component {
	
	render() {
		return(
			<div className="confirm-page">
				<h2>Tack!</h2>
				<p>Din order har nu placerats, leverans kommer att ske innan 16:00 kommande arbetsdag!</p>
				<p>Mail har skickats ut med mer info samt kvitto.</p>
			</div>
		);
	}
}

export default ConSuccess;