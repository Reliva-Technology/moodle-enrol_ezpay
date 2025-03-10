/**
 * Grunt configuration for IIUM EzPay payment gateway plugin
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

module.exports = function(grunt) {
    // Load all grunt tasks.
    grunt.loadNpmTasks("grunt-contrib-clean");
    grunt.loadNpmTasks("grunt-contrib-uglify");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-sass");
    grunt.loadNpmTasks("grunt-eslint");

    // Project configuration.
    grunt.initConfig({
        eslint: {
            // Check JS files for errors.
            amd: {
                src: ["amd/src/**/*.js"]
            }
        },
        uglify: {
            // Minify JS files.
            amd: {
                files: [
                    {
                        expand: true,
                        cwd: "amd/src",
                        src: ["**/*.js"],
                        dest: "amd/build",
                        ext: ".min.js"
                    }
                ]
            }
        },
        watch: {
            // Watch for changes and automatically run tasks.
            amd: {
                files: ["amd/src/**/*.js"],
                tasks: ["amd"]
            }
        },
        clean: {
            // Clean up build files.
            amd: {
                src: ["amd/build"]
            }
        }
    });

    // Register tasks.
    grunt.registerTask("amd", ["eslint:amd", "uglify:amd"]);
    grunt.registerTask("default", ["amd"]);
};
