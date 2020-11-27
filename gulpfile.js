const gulp = require('gulp');

gulp.task("dist", function() {
	return gulp.src(
			[
				"./**/*.php",
				"./**/*.txt",
				"./inc/**",
				"./languages/**",
				"!./tests/**",
				"!./dist/**",
				"!./node_modules/**/*.*",
				"!./vendor/**/*.*",
			],
			{
				base: "./"
			}
		)
		.pipe(gulp.dest("dist/vk-showcase"));
});