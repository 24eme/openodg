<!doctype html>
<!-- ####### PLEASE KEEP ####### -->
<!--[if lte IE 6 ]><html class="no-js ie6 ielt7 ielt8 ielt9" lang="fr"><![endif]-->
<!--[if IE 7 ]><html class="no-js ie7 ielt8 ielt9" lang="fr"><![endif]-->
<!--[if IE 8 ]><html class="no-js ie8 ielt9" lang="fr"><![endif]-->
<!--[if IE 9 ]><html class="no-js ie9" lang="fr"><![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" lang="fr"><!--<![endif]-->
<!-- ####### PLEASE KEEP ####### -->
    <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>

        <link rel="shortcut icon" type="image/x-icon" href="/favico_provence.ico" />
        <link rel="icon" type="image/x-icon" href="/favico_provence.ico" />
        <link rel="icon" type="image/png" href="/favico_provence.png" />

        <?php include_stylesheets() ?>

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,600" rel="stylesheet" type="text/css">
        <link href="/css/style_provence.css?201803141452" rel="stylesheet" type="text/css">

        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    </head>
    <body role="document">

        <div id="header">
            <?php echo include_partial('global/header'); ?>

            <?php include_component('global', 'nav'); ?>
        </div>

        <section id="content" class="container">
                  <div><p style="color:red; text-align:center; font-weight: bold;">VUEJS APP</p></div>

                <?php echo $sf_content ?>
        </section>

        <footer id="footer" class="container hidden-xs hidden-sm text-center" role="contentinfo">
            <nav role="navigation">
                <ul class="list-inline" style="font-size: 13px;">
                    <li><a href="<?php echo url_for('contact') ?>">Contact</a></li>
                    <li><a href="<?php echo url_for('mentions_legales') ?>">Mentions légales</a></li>
                </ul>
            </nav>
        </footer>
    </body>
</html>
