import React from 'react';

function Pickup(props){
	return(
        <option value={props.name} selected={props.selected}>{props.name}</option>
    );
}

export default Pickup;
