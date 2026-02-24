import react from 'eslint-plugin-react';
import globals from 'globals';
import babelParser from '@babel/eslint-parser';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import js from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier';
import typescriptParser from '@typescript-eslint/parser';
import typescriptPlugin from '@typescript-eslint/eslint-plugin';
import { defineConfig } from 'eslint/config';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default defineConfig([
  {
    files: ['**/*.{js,mjs,cjs,jsx}'],
    plugins: {
      react,
    },
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.jquery,
        ...globals.node,
        Promise: 'readonly',
        Set: 'readonly',
        Map: 'readonly',
        am_template_plugin_data: 'readonly',
      },
      parser: babelParser,
      ecmaVersion: 6,
      sourceType: 'module',
      parserOptions: {
        allowImportExportEverywhere: false,
        codeFrame: false,
        sourceType: 'module',
      },
    },
    settings: {
      react: {
        version: 'detect',
      },
    },
    rules: {
      'prettier/prettier': 0,
      'react/prop-types': 0,
      'react/no-unknown-property': [
        'error',
        {
          ignore: ['css'],
        },
      ],
    },
    extends: [
      js.configs.recommended,
      react.configs.flat.recommended, // This is not a plugin object, but a shareable config object
      react.configs.flat['jsx-runtime'], // Add this if you are using React 17+
    ],
  },
  {
    files: ['**/*.{ts,mts,cts,tsx}'],
    plugins: {
      react,
      '@typescript-eslint': typescriptPlugin,
    },
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.jquery,
        ...globals.node,
        Promise: 'readonly',
        Set: 'readonly',
        Map: 'readonly',
        am_template_plugin_data: 'readonly',
      },
      parser: typescriptParser,
      ecmaVersion: 6,
      sourceType: 'module',
      parserOptions: {
        allowImportExportEverywhere: false,
        codeFrame: false,
        sourceType: 'module',
        project: './tsconfig.json', // Ensure you have a tsconfig.json file in your project
      },
    },
    settings: {
      react: {
        version: 'detect',
      },
    },
    rules: {
      'prettier/prettier': 0,
      'react/prop-types': 0,
      '@typescript-eslint/no-unused-vars': 'error',
      '@typescript-eslint/explicit-function-return-type': 'off',
      '@typescript-eslint/no-explicit-any': 'off',
      'react/no-unknown-property': [
        'error',
        {
          ignore: ['css'],
        },
      ],
    },
  },
  eslintConfigPrettier,
]);
