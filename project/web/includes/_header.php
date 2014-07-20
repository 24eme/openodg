<!doctype html>
<!-- ####### PLEASE KEEP ####### -->
<!--[if lte IE 6 ]><html class="no-js ie6 ielt7 ielt8 ielt9" lang="<?php echo LANG; ?>"><![endif]-->
<!--[if IE 7 ]><html class="no-js ie7 ielt8 ielt9" lang="<?php echo LANG; ?>"><![endif]-->
<!--[if IE 8 ]><html class="no-js ie8 ielt9" lang="<?php echo LANG; ?>"><![endif]-->
<!--[if IE 9 ]><html class="no-js ie9" lang="<?php echo LANG; ?>"><![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" lang="<?php echo LANG; ?>"><!--<![endif]-->
<!-- ####### PLEASE KEEP ####### -->
<head>
	<title>
		<?php if($cat_current != "cat_home") { ?>
			<?php if($cat_title != $page_title) echo $page_title." | "; ?>
			<?php echo $cat_title." | "; ?>
		<?php } ?>
		<?php echo SITE_NAME.' - '.SITE_BASELINE; ?>
	</title>
	
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="author" content="Actualys" />
	<meta name="description" content="<?php echo SITE_NAME; ?>" /> 
	<meta name="robots" content="index,follow" />
	<meta name="content-language" content="<?php echo LANG; ?>-FR" /> 
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	
	
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo IMG_PATH; ?>favicon.ico" />
    <link rel="icon" type="image/png" href="<?php echo IMG_PATH; ?>favicon.png" />
    <link rel="apple-touch-icon" href="<?php echo IMG_PATH; ?>apple-touch-icon.png" />
	
	<!-- Fichier compilé par LESS -->
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_PATH.CSS_FILE; ?>.css" media="all" />
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_PATH; ?>print.css" media="print" />

	<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,700,600" rel="stylesheet" type="text/css">	
	
	<script type="text/javascript">
		var _siteLang = "<?php echo LANG; ?>";
		var _jsPath = "<?php echo JS_PATH; ?>";
		var _ajaxPath = "../ajax.php";
	</script>
	
	<script type="text/javascript" src="<?php echo JS_PATH; ?>lib/modernizr-2.8.2.js"></script>
	<script type="text/javascript" src="<?php echo JS_PATH; ?>lib/device.min.js"></script>
	<!--[if lt IE 9]>
      <script type="text/javascript" src="<?php echo JS_PATH; ?>lib/respond.min.js"></script>
    <![endif]-->
</head>

<body role="document" class="<?php echo $cat_current; ?>">

<!-- ####### PLEASE KEEP ####### -->
<!--[if lte IE 7 ]>
<div id="message_ie">
	<div class="gabarit">
		<p><strong>Vous utilisez un navigateur obsolète depuis près de 10 ans !</strong> Il est possible que l'affichage du site soit fortement altéré par l'utilisation de celui-ci.</p>
	</div>
</div>
<![endif]-->
<!-- ####### PLEASE KEEP ####### -->

<!-- #page -->
<div id="page" class="container">

	<div id="bg-page">
		<img src="../images/bg/bg_global.jpg" alt="" />
	</div>

	<!-- #header -->
	<header id="header" class="container" role="banner">

		<h1 class="sr-only">Bienvenue sur le portail de l'association des viticulteurs d'alsace</h1>
			
		<div id="logo">
			<a href="home.php" title="<?php echo SITE_NAME.' - '.SITE_BASELINE; ?> | Retour à la page d'accueil">
				<img src="<?php echo IMG_PATH; ?>logo_site.png" alt="<?php echo SITE_NAME.' - '.SITE_BASELINE; ?>" />
			</a>
		</div>
					
		<nav id="navigation" role="navigation">
			<span class="profile-name">Vincent Rodriguez</span>

			<ul>
				<li><a href="#">Mon compte</a></li>
				<li><a href="#">Mes déclarations</a></li>
				<li><a href="#">Administration</a></li>
				<li><a href="#">Déconnexion</a></li>
			</ul>
		</nav>
	</header>
	<!-- end #header -->