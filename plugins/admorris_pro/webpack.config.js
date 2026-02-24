const path = require('path');
const webpack = require('webpack');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');

module.exports = (env, argv) => {
  const production = argv.mode === 'production';

  return {
    entry: {
      app: ['./src/js/App.jsx'],
    },
    resolve: {
      modules: [path.resolve(__dirname, 'src/js/'), 'node_modules'],
      fallback: {
        fs: false,
        path: false,
        os: false,
      },
      extensions: ['.js', '.jsx', '.ts', '.tsx'],
    },
    watch: !production ? true : false,
    devtool: production ? false : 'eval',
    mode: production ? 'production' : 'development',
    externals: {
      jquery: 'jQuery',
      codemirror: 'CodeMirror',
    },
    module: {
      rules: [
        {
          test: /\.(js|jsx|ts|tsx)$/,
          exclude: /(node_modules|vendor)/,
          use: [
            {
              loader: 'babel-loader',
            },
          ],
        },
        {
          test: /\.css$/i,
          use: ['style-loader', 'css-loader'],
        },
        {
          test: /\.(png|jpe?g|gif)$/i,
          use: [
            {
              loader: 'file-loader',
              options: {},
            },
          ],
        },
        {
          test: /\.svg$/i,
          issuer: /\.[jt]sx?$/,
          use: ['@svgr/webpack'],
        },
      ],
      exprContextRegExp: /$^/,
      exprContextCritical: false,
    },
    output: {
      filename: '[name].[contenthash].js',
      chunkFilename: '[name].[contenthash].bundle.js',
      path: path.resolve(__dirname, 'adminmenu/js'),
      publicPath: '../plugins/admorris_pro/adminmenu/js/',
      clean: true,
    },
    optimization: {
      moduleIds: 'deterministic',
    },
    plugins: [
      new webpack.ProvidePlugin({
        process: 'process/browser',
      }),
      new webpack.ContextReplacementPlugin(/caniuse-lite[\/\\]data[\/\\]regions/, /^$/),
      new webpack.ContextReplacementPlugin(/moment[/\\]locale$/, /de/),
      new SpriteLoaderPlugin(),
      new WebpackManifestPlugin({
        publicPath: '',
      }),
      // new BundleAnalyzerPlugin({ openAnalyzer: false }),
    ],
    cache: true,
    target: ['web', 'es2020'],
    ignoreWarnings: [
      {
        message:
          /require function is used in a way in which dependencies cannot be statically extracted/, // Suppress specific warning messages
      },
    ],
    watchOptions: {
      ignored: ['**/node_modules', '**/vendor'],
    },
  };
};
