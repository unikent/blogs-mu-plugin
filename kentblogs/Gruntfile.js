'use strict';
module.exports = function(grunt) {
  // Load all tasks
  require('load-grunt-tasks')(grunt);
  // Show elapsed time
  require('time-grunt')(grunt);

  grunt.initConfig({
    less: {
      build: {
        files: {
          'blogs-footer/kent-blogs-footer.css': [
            'blogs-footer/kent-blogs-footer.less'
          ]
        },
        options: {
          compress: true
        }
      }
    }
  });

  // Register tasks
  grunt.registerTask('default', [
    'build'
  ]);
  grunt.registerTask('build', [
    'less'
  ]);
};
