const path = require("path");
const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
	...defaultConfig,
	entry: "./src/index.js",
	module: {
		...defaultConfig.module,
		rules: [
			{
				test: /\.(ts|tsx)?$/,
				use: [
					{
						loader: "ts-loader",
					},
				],
				exclude: /node_modules/,
			},
			{
				test: /\.(js|jsx)$/,
				exclude: /node_modules/,
				use: {
					loader: "babel-loader",
					options: {
						presets: ["@babel/preset-env", "@babel/preset-react"],
					},
				},
			},
			...defaultConfig.module.rules,
		],
	},
	resolve: {
		...defaultConfig.resolve,
		extensions: [".tsx", ".ts", ".js", ".jsx"],
	},

	output: {
		...defaultConfig.output,
		filename: "index.js",
		path: path.resolve(__dirname, "build"),
	},
};
