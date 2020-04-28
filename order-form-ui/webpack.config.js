const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

defaultConfig.entry.index = path.join(__dirname, 'index.js');
defaultConfig.output.path = path.join(__dirname, '../pizzakit-plugin/order-form');

module.exports = defaultConfig;