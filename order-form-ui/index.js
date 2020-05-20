import { registerBlockType } from '@wordpress/blocks';
import React from 'react';
import ReactDOM from 'react-dom';
import App from './src/App';
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
	ReactDOM.render(<App defaultChild={OrderForm} defaultChildProps={{ post_address: WPURLS.siteurl }} />, root);
}