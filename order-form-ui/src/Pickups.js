import React from 'react';

function Pickup(props){
	return(
        <option value={props.name}>{props.name}</option>
    );
}

export default Pickup;