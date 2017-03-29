module.exports = function(grunt) {
    // Chargement automatique de tous nos modules
    require('load-grunt-tasks')(grunt);

    // Configuration des plugins
    grunt.initConfig({
        cssmin: {
            combine: {
                options:{
                    report: 'gzip',
                    keepSpecialComments: 0
                },
                files: {
                    'web/built/app.min.css': [
                        'web/css/*.css'
                    ]
                }
            }
        },
        uglify: {
            options: {
                mangle: false,
                sourceMap: true,
                sourceMapName: 'web/built/app.map'
            },
            dist: {
                files: {
                    'web/built/app.min.js':[
                        'web/js/*.js'
                    ]
                }
            }
        },
        watch: {
            css: {
                files: ['web/css/*.css'],
                tasks: ['css']
            },
            javascript: {
                files: ['web/js/*.js'],
                tasks: ['javascript']
            }
        },
        copy: {
            dist: {
                files: [
                    {
                        expand: true,
                        cwd: 'bower_components/jquery/dist/',
                        dest: 'web/built',
                        src: 'jquery.min.js'
                    },
                    {
                        expand: true,
                        cwd: 'bower_components/jquery-ui/',
                        dest: 'web/built',
                        src: 'jquery-ui.min.js'
                    },
                    {
                        expand: true,
                        cwd: 'bower_components/bootstrap/dist/js/',
                        dest: 'web/built',
                        src: 'bootstrap.min.js'
                    },
                    {
                        expand: true,
                        cwd: 'bower_components/select2/dist/js/',
                        dest: 'web/built',
                        src: 'select2.min.js'
                    }
                ]
            }
        }
    });

    // Déclaration des différentes tâches
    grunt.registerTask('default', ['css','javascript']);
    grunt.registerTask('css', ['cssmin']);
    grunt.registerTask('javascript', ['uglify']);
    grunt.registerTask('cp', ['copy']);
};
