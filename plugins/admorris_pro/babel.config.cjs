module.exports = {
  presets: [
    ['@babel/preset-react', { runtime: 'automatic' }],
    [
      '@babel/preset-typescript',
      {
        isTSX: true,
        allExtensions: true,
      },
    ],
    [
      '@babel/preset-env',
      {
        targets: {
          browsers: ['last 2 versions', 'not IE 11', 'not dead', '> 2%'],
        },
      },
    ],
  ],
  plugins: ['macros', 'babel-plugin-styled-components'],
};
