/**
 * NanoSupport Grunt Directives
 *
 * @package     NanoSupport
 * @version     1.0.0
 */

module.exports = function(grunt) {

    'use strict';

    // @Grunt: Get our configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        /**
         * Validate files with JSHint
         * @author: https://github.com/gruntjs/grunt-contrib-jshint
         */
        jshint: {
            all: [
                'Gruntfile.js',
                'assets/js/nanosupport.js',
                'assets/js/nanosupport-admin.js',
                'assets/js/nanosupport-dashboard.js'
            ]
        },

        /**
         * Concatenate & Minify Javascript files
         * @author: https://github.com/gruntjs/grunt-contrib-uglify
         */
        uglify: {
            public: {
                options: {
                    sourceMap: true
                },
                files: {
                    'assets/js/nanosupport.min.js': [ 'assets/js/nanosupport.js' ],
                    'assets/js/nanosupport-admin.min.js': [ 'assets/js/nanosupport-admin.js' ],
                    'assets/js/nanosupport-dashboard.min.js': [ 'assets/js/nanosupport-dashboard.js' ]
                },
            }
        },

        /**
         * Compile SCSS files into CSS
         * @author: https://github.com/sindresorhus/grunt-sass/
         */
        sass: {
            dist: {
                options: {
                    sourceMap: false
                },
                files: {
                    'assets/css/nanosupport.css': 'assets/sass/nanosupport.scss',
                    'assets/css/nanosupport-admin.css': 'assets/sass/nanosupport-admin.scss'
                }
            }
        },

        /**
         * Add px fallbacks to em, and vendor prefixes
         * @author: https://github.com/nDmitry/grunt-postcss
         */
        postcss: {
            options: {
                processors: [
                    require('pixrem')(), // add fallbacks for rem units
                    require('autoprefixer')({browsers: 'last 1 versions'}), // add vendor prefixes
                ]
            },
            dist: {
                src: 'assets/css/nanosupport.css'
            }
        },

        /**
         * Minify Stylehseets for production
         * @author: https://github.com/gruntjs/grunt-contrib-cssmin
         */
        cssmin: {
            minify: {
                files: {
                    'assets/css/nanosupport.css': 'assets/css/nanosupport.css',
                    'assets/css/nanosupport-admin.css': 'assets/css/nanosupport-admin.css'
                },
                options: {
                    report: 'min',
                    keepSpecialComments: 0
                }
            }
        },


        /**
         * Watch for changes and do it
         * @author: https://github.com/gruntjs/grunt-contrib-watch
         */
        watch: {
            options: {
                livereload: {
                    port: 9000
                }
            },
            js: {
                files: ['assets/js/nanosupport.js'],
                tasks: ['uglify']
            },
            css: {
                files: ['assets/sass/*.scss'],
                tasks: ['sass', 'autoprefixer', 'cssmin']
            }
        }

    });


    // @Grunt: we're using the following plugins
    require('load-grunt-tasks')(grunt);


    // @Grunt: do the following when we will type 'grunt'
    grunt.registerTask('default', ['jshint', 'uglify', 'sass', 'autoprefixer', 'cssmin']);

};
