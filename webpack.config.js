
let path = require('path');
let webpack = require('webpack');
let ExtractTextPlugin = require('extract-text-webpack-plugin');

const devBuild = process.env.NODE_ENV !== 'prod';
const extractCss = new ExtractTextPlugin({
    filename: 'stylesheets/[name].css'
});

const config = {
    context: path.resolve(__dirname, './client'),
    entry: {
        app: './js/app.js'
    },
    output: {
        path: path.resolve(__dirname, './web/assets/'),
        publicPath: '/assets/',
        filename: '[name].js',
        chunkFilename: "[id].[hash].bundle.js"
    },
    devtool: 'source-map',
    module: {
        rules: [
            {
                test:    /\.js$/i,
                exclude: [/node_modules/],
                use: 'babel-loader'
            },
            {
                test: /\.scss$/i,
                use: extractCss.extract({
                    fallback: 'style-loader',
                    use: [
                        {
                            loader: 'css-loader',
                            options: {
                                sourceMap: true,
                            }
                        },
                        {
                            loader: 'sass-loader',
                            options: {
                                sourceMap: true,
                            }
                        }
                    ]
                })
            },
            {
                test: /\.jpg$/i,
                use: 'file-loader'
            },
            {
                test: /\.png$/i,
                use: {
                    loader: 'url-loader',
                    options: {
                        limit: 10000
                    }
                }
            }
        ],
    },
    plugins: [
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: function (module) {
                return module.context && module.context.indexOf('node_modules') !== -1;
            }
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'manifest'
        }),
        new webpack.ProvidePlugin({
            $: 'jquery'
        }),
        extractCss
    ]
};

if (devBuild) {
    console.log('Webpack dev build');
} else {
    console.log('Webpack production build');
    config.plugins.push(
        new webpack.optimize.DedupePlugin()
    );
    config.plugins.push(
        new webpack.optimize.UglifyJsPlugin({
            compress: {
                warnings: true
            }
        })
    );
}

module.exports = config;
