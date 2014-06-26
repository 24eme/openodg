<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

<script type="text/javascript">
if(typeof jQuery == 'undefined')
{
	document.write(unescape("%3Cscript src='<?php echo JS_PATH; ?>lib/jquery-1.11.0.min.js' type='text/javascript'%3E%3C/script%3E"));
}
</script>

<script type="text/javascript" src="<?php echo JS_PATH; ?>lib/bootstrap.min.js"></script>

<!--[if lte IE 9]>
  <script src="<?php echo JS_PATH; ?>plugins/ie-bootstrap-carousel.min.js"></script>
<![endif]-->

<script type="text/javascript" src="<?php echo JS_PATH; ?>plugins/jquery.plugins.min.js"></script>

<?php if(isset($extra_js)): ?>
	<?php foreach($extra_js as $js): ?>
		<script type="text/javascript" src="<?php echo JS_PATH; ?><?php echo $js; ?>"></script>
	<?php endforeach; ?>
<?php endif; ?>

<script type="text/javascript" src="<?php echo JS_PATH; ?>global.js"></script>
