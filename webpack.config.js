const path = require('path');
const webpack = require('webpack');

module.exports = function(env) {
    "use strict";
    return {
        context: path.resolve(__dirname, './web/bundles/app/'),
        entry: {
            app: ['./javascripts/app.js']
        },
        output: {
            path: path.resolve(__dirname, './web/assets/'),
            publicPath: "/assets/",
            filename: '[name].js',
        },
        plugins: [
            new webpack.optimize.CommonsChunkPlugin({
                name: 'vendor',
                minChunks: function (module) {
                    return module.context && module.context.indexOf('node_modules') !== -1;
                },
            }),
            new webpack.optimize.CommonsChunkPlugin({
                name: 'manifest'
            })
        ],
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: [/node_modules/],
                    use: [{
                        loader: 'babel-loader',
                        options: { presets: ['es2015'] },
                    }],
                },
                {
                    test: require.resolve('jquery'),
                    use: [
                        { loader: 'expose-loader', options: 'jQuery' },
                        { loader: 'expose-loader', options: '$' }
                    ]
                },
                {
                    test: require.resolve('tether'),
                    use: [
                        { loader: 'expose-loader', options: 'Tether' }
                    ]
                },
            ]
        },
    }
};
