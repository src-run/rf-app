
/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

const webpack = require('webpack');

const plugins = {
    cleanup:     require('webpack-cleanup-plugin'),
    extractText: require('extract-text-webpack-plugin'),
    manifest:    require('webpack-manifest-plugin'),
    md5:         require('webpack-md5-hash'),
    path:        require('path'),
};

const origins = {
    context: plugins.path.resolve(__dirname, './client'),
    entries: { app: './js/app.js' },
};

const outputs = {
    fileNameTpl: process.env.NODE_ENV === 'prod' ? '[name].[chunkhash]' : '[name]',
    outsidePath: '/assets/',
    privatePath: plugins.path.resolve(__dirname, `./web/assets`),
};

const config = {
    devtool: 'source-map',
    context: origins.context,
    entry:   origins.entries,
    output: {
        path:       outputs.privatePath,
        publicPath: outputs.outsidePath,
        filename:   `${outputs.fileNameTpl}.js`,
    },
    module: {
        rules: [
            { test: /\.js$/i, exclude: [/node_modules/], use: 'babel-loader' },
            { test: /\.scss$/i, use: plugins.extractText.extract({ fallback: 'style-loader', use: [
                { loader: 'css-loader', options: { sourceMap: true } },
                { loader: 'postcss-loader', options: { sourceMap: true, plugins: (loader) => [
                    require('postcss-import')({ root: loader.resourcePath }),
                    require('autoprefixer')(),
                    require('cssnano')(),
                    require('postcss-browser-reporter')(),
                    require('postcss-reporter')(),
                ] } },
                { loader: 'sass-loader', options: {  sourceMap: true  } },
            ]})},
            { test: /\.jpg$/i, use: 'file-loader' },
            { test: /\.png$/i, use: { loader: 'url-loader', options: { limit: 10000 } } },
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
    ],
};

if (process.env.NODE_ENV === 'production') {
    config.plugins.push(new webpack.NoEmitOnErrorsPlugin());
    config.plugins.push(new plugins.md5());
    //config.plugins.push(new webpack.optimize.UglifyJsPlugin({ compress: { warnings: false } }));
}

module.exports = config;
