const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

defaultConfig.entry.index = path.join(__dirname, 'index.js');
defaultConfig.output.path = path.join(__dirname, '../pizzakit-plugin/order-form');

defaultConfig.module.rules.push({
	test: /.s?[ac]ss$/,
	use: [
		'style-loader',
		'css-loader',
		'sass-loader'
	]
});
defaultConfig.module.rules.push({
	test: /.(png|jpg)$/,
	use: [
		'url-loader'
	]
});

module.exports = defaultConfig;