const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    
    entry: {
        // Frontend
        'frontend': './assets/js/frontend/main.js',
        'frontend-components': './assets/js/frontend/components/index.js',
        
        // Admin
        'admin': './assets/js/admin/main.js',
        'admin-dashboard': './assets/js/admin/dashboard.js',
        
        // Blocks
        'blocks': './assets/js/blocks/index.js',
        
        // Styles
        'frontend-styles': './assets/scss/frontend/main.scss',
        'admin-styles': './assets/scss/admin/main.scss'
    },
    
    output: {
        path: path.resolve(__dirname, 'build'),
        filename: 'js/[name].js',
        clean: true
    },
    
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            ...defaultConfig.resolve.alias,
            '@cupompromo': path.resolve(__dirname, 'assets/js'),
            '@scss': path.resolve(__dirname, 'assets/scss')
        }
    }
}; 