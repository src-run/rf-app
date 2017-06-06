
const path = require('path');
const webpack = require('webpack');

const plugins = {
    cleanup:     require('webpack-cleanup-plugin'),
    extractText: require('extract-text-webpack-plugin'),
    manifest:    require('webpack-manifest-plugin'),
    md5:         require('webpack-md5-hash'),
};

const origins = {
    context: path.resolve(__dirname, './client'),
    entries: { app: './js/app.js' }
};

const outputs = {
    outsidePath: '/assets/',
    privatePath: path.resolve(__dirname, `./web/assets`),
    fileNameTpl: process.env.NODE_ENV === 'prod' ? '[name].[chunkhash]' : '[name]'
};

const config = {
    devtool: 'source-map',
    context: origins.context,
    entry:   origins.entries,
    output: {
        path:       outputs.privatePath,
        publicPath: outputs.outsidePath,
        filename:   `${outputs.fileNameTpl}.js`
    },
    module: {
        rules: [
            { test: /\.js$/i, exclude: [/node_modules/], use: 'babel-loader' },
            { test: /\.scss$/i, use: plugins.extractText.extract({ fallback: 'style-loader', use: [
                { loader: 'css-loader', options: { sourceMap: true } },
                { loader: 'postcss-loader', options: { sourceMap: true,
                    plugins: (loader) => [
                        require('postcss-import')({ root: loader.resourcePath }),
                        require('autoprefixer')(),
                        require('cssnano')(),
                        require('postcss-browser-reporter')(),
                        require('postcss-reporter')(),
                    ]
                } },
                { loader: 'sass-loader', options: {  sourceMap: true  } }
            ]})},
            { test: /\.jpg$/i, use: 'file-loader' },
            { test: /\.png$/i, use: { loader: 'url-loader', options: { limit: 10000 } } }
        ],
    },
    plugins: [
        new webpack.optimize.CommonsChunkPlugin({ name: 'vendor', minChunks: (module) => {
            return module.context && module.context.indexOf('node_modules') !== -1;
        }}),
        new webpack.optimize.CommonsChunkPlugin({ name: 'manifest' }),
        new webpack.ProvidePlugin({ $: 'jquery' }),
        new plugins.manifest({ publicPath: outputs.outsidePath }),
        new plugins.extractText({ filename: `${outputs.fileNameTpl}.css` }),
        new plugins.cleanup(),
    ]
};

if (process.env.NODE_ENV === 'prod') {
    config.plugins.push(new webpack.optimize.UglifyJsPlugin({ compress: { warnings: true } }));
    config.plugins.push(new plugins.md5())
}

module.exports = config;
