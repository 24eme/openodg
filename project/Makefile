all: web/css/compile.css

web/css/compile.css: web/less/compile.less web/less/fonts.less web/less/bootstrap_vars.less web/less/bootstrap.less web/less/toolbox.less web/less/bootstrap_datetimepicker.less web/less/bootstrap_select2.less web/less/global.less web/less/main.less
	lessc web/less/compile.less > web/css/compile.css
