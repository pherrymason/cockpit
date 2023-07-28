const path = require('path');
const Dotenv = require('dotenv-webpack');
const webpack = require('webpack');

const config = {
    entry: {
       // 'collection.entries': ['./src/ui/collection.entries.ts'],
        'collection.entry': ['./src/ui/collection.entry.js']
    },
    output: {
        path: path.resolve(__dirname, 'assets/app/js'),
        filename: '[name].js',
    },
    resolve: {
        extensions: ['.*', '.js', ".vue", '.json'],
        modules: [path.resolve(__dirname, 'node_modules')],
    },
    module: {
        rules: [
            { parser: { amd: false } },
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader",
                },
            },
        ],
    },
    plugins: [],
    devtool: 'eval'
};

module.exports = (env, argv) => {
    let apiEndpoint = '';
    if (argv.mode === 'production') {
        apiEndpoint = 'https://www.theexperialist.com/admin/';
    } else {
        apiEndpoint = 'http://localhost:9999/admin/';
        config.devtool = 'source-map';
    }

    config.plugins.push(
        new webpack.DefinePlugin({
            API_ENDPOINT: JSON.stringify(apiEndpoint),
        })
    );

    return config;
};