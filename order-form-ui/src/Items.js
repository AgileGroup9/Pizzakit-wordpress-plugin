import React from 'react';

/*
    A number select widget
        features a decrement and increment button with a display in the middle
        uses:
            props.count for display
            props.onClick which expects an function that takes a int delta as param.
*/
function Number_select(props){
	return(
		<div className="number-select">
			<button className="btn btn-primary" id="minus" onClick={() => props.onClick(-1)}>-</button>
			{props.count}
			<button className="btn btn-primary" id="plus" onClick={ () => props.onClick(1)}>+</button>
		</div>
	);
}

/*

Big item card with counter, not currently used

function Big_item(props){
    return(
        <div className="big-item">
            <img src={picollo_img} alt={props.name}></img>
            <h3>{props.name}</h3>
            <Number_select count={props.count} onClick={(delta) => props.onClick(props.name,delta)}/>
        </div>
    );
} */


/*
    An inline small item widget
        displays a label, followed by a counter, optionaly followed by a description
            uses:
                props.name as a label
                props.count as a current value
                props.onClick which expects an string followed by a integer delta as param
            optional:
                props.desc as a short description following the counter
*/
function Small_item(props){
	return(
		<div className="small-item inpt">
			<label>{props.name} {props.desc ? <small>{props.desc}</small> : null}</label>
			<Number_select count={props.count} onClick={(delta) => props.onClick(props.name,delta)}/>
		</div>
	);
}

export default Small_item;