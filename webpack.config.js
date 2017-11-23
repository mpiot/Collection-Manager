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
    .createSharedEntry('js/vendor', ['jquery', 'jquery-ui', 'bootstrap-sass', 'select2'])
    .addEntry('js/app', [
        './assets/js/advanced-search.js',
        './assets/js/charmap.js',
        './assets/js/collection-type.js',
        './assets/js/delay.js',
        './assets/js/form-handle.js',
        './assets/js/form-primer.js',
        './assets/js/form-send.js',
        './assets/js/form-species.js',
        './assets/js/form-strain.js',
        './assets/js/form-group.js',
        './assets/js/form-plasmid.js',
        './assets/js/images.js',
        './assets/js/list-filter.js',
        './assets/js/modal-confirmation-message.js',
        './assets/js/popover.js',
        './assets/js/select2-conf.js',
        './assets/js/strain-tubes-dynamic-on-box-change.js'
    ])
    .addStyleEntry('css/app', ['./assets/scss/app.scss'])
;

var config = Encore.getWebpackConfig();
config.externals = {googleMaps: 'google.maps'};

module.exports = config;
