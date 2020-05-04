import { registerBlockType } from '@wordpress/blocks';
import React from 'react';
import ReactDOM from 'react-dom';
import OrderForm from './src/OrderForm';

registerBlockType(
	'pizzakit/order-form',
	{
		title: 'Pizzakit: Order form',
		icon: 'feedback',
		category: 'common',
		edit: () => <p>Pizzakit: Order form (placeholder)</p>,
		save: () => null
	}
);

const root = document.getElementById('pizzakit-order-form');
if (root != null) {
	ReactDOM.render(<OrderForm post_address="/"/>, root);
}