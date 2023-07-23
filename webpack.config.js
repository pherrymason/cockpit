const path = require('path');
const Dotenv = require('dotenv-webpack');

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
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader",
                },
            },
        ],
    },
    plugins: [
        new Dotenv({
            systemvars: true,
            path: '../../../.env'
        }),
        ]
};

module.exports = config;