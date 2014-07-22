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
        
        <link rel="shortcut icon" type="image/x-icon" href="/images/favicon.ico" />
        <link rel="icon" type="image/png" href="/images/favicon.png" />

        <?php include_stylesheets() ?>
        
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,700,600" rel="stylesheet" type="text/css">

        <script type="text/javascript" src="/js/lib/modernizr-2.8.2.js"></script>
        <script type="text/javascript" src="/js/lib/device.min.js"></script>

        <!--[if lt IE 9]>
            <script type="text/javascript" src="/js/lib/respond.min.js"></script>
        <![endif]-->
    </head>
    <body role="document">
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
                <img src="/images/bg/bg_global.jpg" alt="" />
            </div>

            <!-- #header -->
            <header id="header" class="container" role="banner">

                <h1 class="sr-only">Bienvenue sur le portail de l'association des viticulteurs d'alsace</h1>
                    
                <div id="logo">
                    <a href="<?php echo url_for("home") ?>" title="AVA - Association des viticulteurs d'alsace | Retour à la page d'accueil">
                        <img src="/images/logo_site.png" alt="AVA - Association des viticulteurs d'alsace" />
                    </a>
                </div>
                            
                <nav id="navigation" role="navigation">
                    <span class="profile-name">Vincent Rodriguez</span>

                    <ul>
                        <li><a href="#">Mon compte</a></li>
                        <li><a href="#">Mes déclarations</a></li>
                        <li><a href="#">Administration</a></li>
                        <li><a href="<?php echo url_for('auth_logout') ?>">Déconnexion</a></li>
                    </ul>
                </nav>
            </header>
            <!-- end #header -->
            
            <!-- #content -->
            <section id="content" class="container">
                <?php echo $sf_content ?>
            </section>

                <!-- #footer -->
            <footer id="footer" class="container" role="contentinfo">
                <nav role="navigation">
                    <ul>
                        <li><a href="#">A propos</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Mentions légales</a></li>
                        <li><a href="#">Crédits</a></li>
                    </ul>
                </nav>
            </footer>
            <!-- end #footer -->
        
        </div>
        <!-- end #page -->

        <?php include_javascripts() ?>
    </body>
</html>