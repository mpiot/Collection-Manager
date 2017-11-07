var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery'
    })
    .enableVersioning()
    .enableSourceMaps(!Encore.isProduction())
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: false
    })
    .createSharedEntry('js/vendor', ['jquery', 'bootstrap-sass'])
    .addEntry('js/app', [
        './assets/js/add-entity-on-fly.js',
        './assets/js/advanced-search.js',
        './assets/js/anti-iframe.js',
        './assets/js/charmap.js',
        './assets/js/collection-type.js',
        './assets/js/delay.js',
        './assets/js/field_autocomplete.js',
        './assets/js/images.js',
        './assets/js/modal-confirmation-message.js',
        './assets/js/plasmid-add-gbk-file.js',
        './assets/js/select2-conf.js',
        './assets/js/strain-tubes-dynamic.js'
    ])
    .addStyleEntry('css/app', ['./assets/scss/app.scss'])
;

module.exports = Encore.getWebpackConfig();
