import stylistic from '@stylistic/eslint-plugin';
import js from '@eslint/js';
import prettier from 'eslint-config-prettier/flat';

const controlStatements = ['if', 'return', 'for', 'while', 'do', 'switch', 'try', 'throw'];
const paddingAroundControl = controlStatements.flatMap((statement) => [
    { blankLine: 'always', prev: '*', next: statement },
    { blankLine: 'always', prev: statement, next: '*' },
]);

export default [
    {
        ignores: ['vendor', 'node_modules', 'public', 'bootstrap/cache'],
    },
    js.configs.recommended,
    {
        files: ['eslint.config.js', 'vite.config.js'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                console: 'readonly',
                process: 'readonly',
            },
        },
        plugins: {
            '@stylistic': stylistic,
        },
        rules: {
            curly: ['error', 'all'],
            '@stylistic/brace-style': ['error', '1tbs', { allowSingleLine: false }],
            '@stylistic/padding-line-between-statements': ['error', ...paddingAroundControl],
        },
    },
    prettier,
];
