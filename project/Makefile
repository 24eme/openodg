all: web/css/compile_ava.css web/css/compile_rhone.css web/css/compile_provence.css

web/css/compile_ava.css: web/less/ava/compile.less web/less/ava/fonts.less web/less/ava/variables.less web/less/ava/bootstrap.less web/less/ava/toolbox.less web/less/ava/bootstrap_datetimepicker.less web/less/ava/bootstrap_select2.less web/less/ava/global.less web/less/ava/main.less
	lessc web/less/ava/compile.less > web/css/compile_ava.css

web/css/compile_rhone.css: web/less/rhone/compile.less
	lessc web/less/rhone/compile.less > web/css/compile_rhone.css

web/css/compile_provence.css: web/less/provence/compile.less web/less/provence/variables.less
	lessc web/less/provence/compile.less > web/css/compile_provence.css
