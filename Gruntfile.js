module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        datetime: Date.now(),
        uglify: {
            options: {
                mangle: false
            },
            // Les JS à inclure dans <head>
            head: {
                files: {
                    'public/build/head.min.js': [
                        'public/vendor/jquery/dist/jquery.min.js',
                        'public/vendor/angular/angular.min.js',
                        'public/scripts/ui/translate.js'
                    ]
                }
            },
            // Les JS des libs externes
            vendors: {
                files: {
                    'public/build/vendors.min.js': [
                        'public/vendor/jquery-ui/ui/minified/jquery-ui.min.js',
                        'public/vendor/jquery-confirm/jquery.confirm.min.js',
                        'public/vendor/jquery-form/jquery.form.js',
                        'public/vendor/bootstrap/dist/js/bootstrap.min.js',
                        'public/vendor/intro.js/minified/intro.min.js',
                        'public/vendor/markitup-markitup/markitup/jquery.markitup.js',
                        'public/vendor/markitup-markitup/markitup/sets/default/set.js',
                        'public/vendor/select2/select2.min.js',
                        'public/vendor/angular-bootstrap/ui-bootstrap.min.js',
                        'public/vendor/angular-bootstrap/ui-bootstrap-tpls.min.js',
                        'public/muih/muih.js'
                    ]
                }
            },
            // Notre appli
            app: {
                files: {
                    'public/build/app.min.js': [
                        'public/template/js/app.js',
                        'public/template/js/notification/SmartNotification.min.js',
                        'public/template/js/smartwidgets/jarvis.widget.min.js',
                        'public/scripts/ajax-form.js',
                        'public/scripts/feedback-form.js',
                        'public/scripts/ui/form-action.js',
                        'public/scripts/ui/form-ajax.js',
                        'public/scripts/ui/refRefactor.js'
                    ]
                }
            }
        },
        cssmin: {
            // Les CSS des libs externes
            vendors: {
                files: {
                    'public/build/vendors.min.css': [
                        'public/template/css/bootstrap-custom.min.css',
                        'public/vendor/fontawesome/css/font-awesome.min.css',
                        'public/vendor/intro.js/minified/introjs.min.css',
                        'public/vendor/markitup-markitup/markitup/skins/markitup/style.css',
                        'public/markitup/skin-textile-style.css',
                        'public/vendor/select2/select2.css',
                        'public/muih/muih.css',
                        'public/template/css/smartadmin-production.css'
                    ]
                }
            },
            // Notre appli
            app: {
                files: {
                    'public/build/app.min.css': [
                        'public/css/smartadmin-additions.css',
                        'public/css/af.css',
                        'public/css/orga/navigation.css',
                        'public/css/orga/organizations.css',
                        'public/css/ui/datagrid.css',
                        'public/css/ui/tabs.css',
                        'public/css/ui/tmd.css',
                        'public/css/ui/tree.css'
                    ]
                }
            }
        },
        copy: {
            fonts: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: [ 'public/vendor/fontawesome/fonts/*' ],
                        dest: 'public/fonts/'
                    }
                ]
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-copy');

    grunt.registerTask('default', [
        'uglify:head',
        'uglify:vendors',
        'uglify:app',
        'cssmin:vendors',
        'cssmin:app',
        'copy:fonts'
    ]);

};
