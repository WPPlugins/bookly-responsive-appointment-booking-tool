export default {
    input: 'main.js',
    output: {
        file: '../bookly.js',
        format: 'iife',
        globals: {
            jquery: 'jQuery'
        },
    }
};