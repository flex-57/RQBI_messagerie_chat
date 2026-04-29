const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore
        .configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
        .setOutputPath('public/build/')
        .setPublicPath('/build/')
        .addEntry('app', './assets/app.js')
        .addStyleEntry('css/app', './assets/styles/app.css')
        .splitEntryChunks()
        .enableSingleRuntimeChunk()
        .cleanupOutputBeforeBuild()
        .enableBuildNotifications()
        .enableSourceMaps(!Encore.isProduction())
        .enableVersioning(Encore.isProduction())
        .configureBabelPresetEnv((config) => {
            config.useBuiltIns = 'entry';
            config.corejs = '3.23';
        })
        .enablePostCssLoader()
        .enableSassLoader();
}

module.exports = Encore.getWebpackConfig();
