const path = require('path');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');

module.exports = (env, argv) => {
    const production = argv.mode === 'production';

    return {
        entry: {
            app: ['./js/src/main.js'],
        },
        watch: !production ? true : false,
        devtool: production ? false : 'inline-cheap-module-source-map',
        mode: production ? 'production' : 'development',
        externals: {
            jquery: 'jQuery',
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /(node_modules|bower_components)/,
                    use: {
                        loader: 'babel-loader',
                    },
                    resolve: {
                        extensions: ['.js'],
                    },
                },
            ],
        },
        output: {
            path: path.resolve(__dirname, 'js/admorris'),
            filename: '[name].[contenthash].js',
            chunkFilename: '[name].[contenthash].bundle.js',
            devtoolModuleFilenameTemplate(info) {
                return `file:///${info.absoluteResourcePath.replace(/\\/g, '/')}`;
            },
        },
        optimization: {
            moduleIds: 'deterministic',
        },
        plugins: [
            new WebpackManifestPlugin({
                publicPath: '',
            }),
        ],
        cache: true,
        target: ['web', 'es2020'],
    };
};
