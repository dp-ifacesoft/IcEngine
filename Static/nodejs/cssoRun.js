var gulp = require('gulp');
var gulpCsso = require('gulp-csso');
var filePath = process.argv[2];
var filePathSplited = filePath.split('/');
var fileDir = '';
var fileName = filePathSplited.pop();
for (var i in filePathSplited) {
    fileDir += filePathSplited[i] + '/';
}
gulp.task('default', function() {
    return gulp.src([filePath])
        .pipe(gulpCsso())
        .pipe(gulp.dest(fileDir));
});
gulp.start('default');