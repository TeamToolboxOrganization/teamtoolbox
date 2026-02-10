// This project uses "Yarn" package manager for managing JavaScript dependencies along
// with "Webpack Encore" library that helps working with the CSS and JavaScript files
// that are stored in the "assets/" directory.
//
// Read https://symfony.com/doc/current/frontend.html to learn more about how
// to manage CSS and JavaScript files in Symfony applications.
var Encore = require('@symfony/webpack-encore');
const WorkboxPlugin = require('workbox-webpack-plugin');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .autoProvideVariables({
        "window.Bloodhound": require.resolve('bloodhound-js'),
    })
    .enableSassLoader()
    // when versioning is enabled, each filename will include a hash that changes
    // whenever the contents of that file change. This allows you to use aggressive
    // caching strategies. Use Encore.isProduction() to enable it only for production.
    .enableVersioning(false)
    .addEntry('app', './assets/js/app.js')
    .addEntry('adminlte', './assets/js/adminlte.js')
    .addEntry('page_dashboard', './assets/js/page_dashboard.js')
    .addEntry('page_userListAdmin', './assets/js/page_userListAdmin.js')
    .addEntry('page_categoriesList', './assets/js/page_categoriesList.js')
    .addEntry('base', './assets/js/base.js')
    .addEntry('googleanalytics', './assets/js/googleanalytics.js')
    .addEntry('localstorage', './assets/js/localstorage.js')
    .addEntry('login', './assets/js/login.js')
    .addEntry('mep', './assets/js/mep.js')
    .addEntry('mep_action', './assets/js/mep_action.js')
    .addEntry('custom_color', './assets/js/custom_color.js')
    .addEntry('office_action', './assets/js/office_action.js')
    //.addEntry('user_edit', './assets/js/user_edit.js')
    .addEntry('calendar', './assets/js/calendar.js')
    .addEntry('checkNote', './assets/js/checkNote.js')
    .addEntry('newNote', './assets/js/newNote.js')
    .addEntry('readNote', './assets/js/readNote.js')
    .addEntry('deleteNote', './assets/js/deleteNote.js')
    //.addEntry('snow', './assets/js/snow.js')
    .addEntry('notification', './assets/js/notification.js')
    .addEntry('chart', './assets/js/chart.js')
    .addEntry('planifiedActivities', './assets/js/planifiedActivities.js')
    .addEntry('gantt_staffing', './assets/js/gantt_staffing.js')
    .addEntry('gantt_view', './assets/js/gantt_view.js')
    .addEntry('tuto_dashboard', './assets/js/tuto_dashboard.js')
    .addEntry('desk', './assets/js/desk.js')
    //.addEntry('poc_search', './assets/js/poc_search.js')
    .addEntry('outlook_calendar', './assets/js/outlook_calendar.js')
    .addEntry('OneSignalSDKWorker', './assets/js/OneSignalSDKWorker.js')
    .addEntry('vacation', './assets/js/vacation.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .enableIntegrityHashes(true)
    .configureBabel(null, {
        useBuiltIns: 'usage',
        corejs: 3,
    })
    .addPlugin(new WorkboxPlugin.GenerateSW({
        // these options encourage the ServiceWorkers to get in there fast
        // and not allow any straggling "old" SWs to hang around
        clientsClaim: true,
        skipWaiting: true,
        maximumFileSizeToCacheInBytes: 1024 * 1024 * 5,
        //swDest: "../service-worker.js"
        //importsDirectory: 'build/'
    }))
;

module.exports = Encore.getWebpackConfig();
