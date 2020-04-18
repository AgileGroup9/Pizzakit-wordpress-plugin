(function (blocks, element) {
	var el = element.createElement;

	blocks.registerBlockType(
		"pizzakit/order-form",
		{
			title: "Pizzakit: Order form",
			icon: "feedback",
			category: "common",
			edit: function () {
				return el(
					"p",
					null,
					"Pizzakit: Order form (placeholder)"
				);
			},
			save: function () {
				return el(
					"p",
					null,
					"Pizzakit: Order form (placeholder)"
				);
			},
		}
	);
}(
	window.wp.blocks,
	window.wp.element
));