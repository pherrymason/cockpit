const path = require('path');

const config = {
    entry: {
        'collection.entries': ['./src/ui/collection.entries.ts'],
    },
    output: {
        path: path.resolve(__dirname, 'assets/app/js'),
        filename: '[name].js',
    },
    resolve: {
        extensions: ['.js', '.jsx', '.ts', '.tsx', '.json'],
        modules: [path.resolve(__dirname, 'node_modules')],
    },
    module: {
        rules: [
            {
                test: /\.(js|ts)x?$/,
                use: {
                    loader: 'babel-loader',
                },
                exclude: /node_modules/,
            }
        ]
    }
};

module.exports = config;