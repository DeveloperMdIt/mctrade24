const fs = require('fs');
const path = require('path');

const sassFilesPath = path.join(__dirname, 'sassFiles.json');
const sassFiles = JSON.parse(fs.readFileSync(sassFilesPath, 'utf8'));

const allowedStyles = ['compressed', 'expanded'];
const style = allowedStyles.includes(process.argv[2]) ? process.argv[2] : 'expanded';

const watch = process.argv.includes('--watch') ? '--watch' : '';

const sassCommand = Object.entries(sassFiles)
    .map(([input, output]) => `sass/${input}:admorris/${output}`)
    .join(' ');

console.log(
    `cd styles && sass --style=${style} --embed-sources --silence-deprecation=import,global-builtin,mixed-decls,color-functions ${sassCommand} ${watch}`
);
