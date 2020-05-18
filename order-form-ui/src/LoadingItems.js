import React from 'react';
import { Number_select } from './Items';

function Loading_item(props){
	return(
		<div className="loading-item small-item inpt">
			<label><span>Named item</span> {props.desc ? <small>small desc</small> : null} {props.price ? <span>(42 kr)</span> : null}</label>
			<Number_select count={0}/>
		</div>
	);
}

export default Loading_item;