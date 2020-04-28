import { registerBlockType } from '@wordpress/blocks';

registerBlockType(
	'pizzakit/order-form',
	{
		title: 'Pizzakit: Order form',
		icon: 'feedback',
		category: 'common',
		edit: () => <p>Pizzakit: Order form (placeholder)</p>,
		save: () => <p>Pizzakit: Order form (placeholder)</p>
	}
);