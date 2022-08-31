const {src,dest,series,parallel,watch} = require('gulp');
const del = require('del');
const browserSync = require('browser-sync').create();
const sass = require('gulp-sass')(require('node-sass'));
const sourcemaps = require('gulp-sourcemaps');
const minify = require('gulp-minify');
const imagemin = require('gulp-imagemin');
const origin = './src/';
const destination = './';

async function clean(cb) {
    cleanCss(cb);
    cleanImage(cb);
    cleanJs(cb);
    cb();
}

async function cleanCss(cb) {
    await del(destination + 'css/*');
    cb();
}

async function cleanImage(cb) {
    await del(destination + 'images/*');
    cb();
}

async function cleanJs(cb) {
    await del(destination + 'js/*');
    cb();
}

function css(cb) {
    cleanCss(cb);
    src(origin + 'scss/style.scss')
    .pipe(sourcemaps.init())
    .pipe(
        sass({
            sourcemap: true,
            outputStyle: 'compressed'
        })
    )
    .pipe(dest(destination + 'css'));
    cb();
}

function js(cb) {
    cleanJs(cb);
    src(
        origin + 'js/main.js',
        )
        .pipe(minify({
            ext:{
                min:'.js'
            },
            noSource: true
        }))
        .pipe(dest(destination + 'js'));
    cb();
}
function images(cb) {
    cleanImage(cb);
    src(
        origin + 'images/*',
        )
        .pipe(imagemin())
		.pipe(dest(destination + 'images'));
    cb();
}
function watcher(cb) {
    watch(origin + 'images/*.{png,jpg,jpeg,gif,svg}').on('all', series(images, browserSync.reload));
    watch(origin + '**/*.scss').on('change', series(css, browserSync.reload));
    watch(origin + '**/*.js').on('change', series(js, browserSync.reload));
    cb();
}

async function server(cb) {
    await browserSync.init({
        proxy: 'https://victro.me/vfphp/wordpress/marine/calculator/',
        port: 443,
    })
    cb();
}


exports.default = series(clean, parallel(css, js, images), server, watcher);
