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

        <link rel="shortcut icon" type="image/x-icon" href="/favico_ava.ico" />
        <link rel="icon" type="image/x-icon" href="/favico_ava.ico" />
        <link rel="icon" type="image/png" href="/favico_ava.png" />

        <?php include_stylesheets() ?>

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,600" rel="stylesheet" type="text/css">

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

        <div id="page" class="container">

            <div id="bg-page" class="hidden-xs hidden-sm">
                <img src="/images/bg/bg_global.jpg" alt="" />
            </div>

            <header id="header" class="container <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN) || $sf_params->get('modeMobile')): ?>hidden-xs hidden-sm<?php endif; ?>" role="banner">
                <div id="logo">
                    <a href="<?php echo url_for('accueil') ?>" title="AVA - Association des viticulteurs d'alsace | Retour à la page d'accueil">
                        <img src="/images/logo_site.png" alt="AVA - Association des viticulteurs d'alsace" />
                    </a>
                </div>
                <h1 id="header_titre" class="sr-only">Portail de l'association<br /> des viticulteurs d'alsace</h1>
                <?php use_helper('Text'); ?>
                <?php if($sf_user->isAuthenticated()): ?>
                <?php if($sf_user->getCompte()): ?>
                <nav class="<?php if($sf_user->getEtablissement()): ?>bloc-right<?php endif; ?>" id="navigation-admin" role="navigation">
                    <span class="profile-name"><?php echo $sf_user->getCompte()->nom ?></span>
                    <ul>
                        <li><a href="<?php echo url_for('redirect_to_mon_compte_civa'); ?>">Mon compte</a></li>
                        <li><a href="<?php echo url_for('auth_logout') ?>">Déconnexion</a></li>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php if($sf_user->getEtablissement()): ?>
                <nav id="navigation" role="navigation">
                    <span class="profile-name"><?php echo str_replace(" ", "&nbsp;", truncate_text(preg_replace('/(EARL|SCEA|SARL|SAS|SA|GAEC|Distillerie)(.*)/', "$2", $sf_user->getEtablissement()->nom),30)); ?></span>
                    <ul>
                        <li><a href="<?php echo url_for('accueil') ?>">Mes déclarations AVA</a></li>
                        <li><a href="<?php echo sfConfig::get('app_url_civa') ?>">Mon espace CIVA</a></li>
                        <li><a href="<?php echo url_for('mon_compte'); ?>">Mon compte</a></li>
                        <?php if(!$sf_user->getCompte()): ?>
                        <li><a href="<?php echo url_for('auth_logout') ?>">Déconnexion</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
                <?php if($sf_user->isAuthenticated() && !$sf_user->getCompte() && !$sf_user->getEtablissement()): ?>
                    <nav id="navigation" role="navigation">
                        <ul>
                            <li><a href="<?php echo url_for('auth_logout') ?>">Déconnexion</a></li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </header>

            <?php if($sf_user->isAuthenticated() && ($sf_user->isAdmin() || $sf_user->getEtablissement())): ?>
            <div class="container <?php if($sf_params->get('modeMobile')): ?>hidden-xs hidden-sm<?php endif; ?>" style="padding: 0; margin: 0;">
                <?php include_partial('global/nav'); ?>
            </div>
            <?php endif; ?>

            <section id="content" class="container">
              <?php if(sfConfig::get('app_instance') == 'preprod' ): ?>
                <div><p style="color:red; text-align:center; font-weight: bold;">Preproduction (la base est succeptible d'être supprimée à tout moment)</p></div>
              <?php endif; ?>
                <div style="margin-bottom: 20px;"></div>
                <?php echo $sf_content ?>
            </section>

            <footer id="footer" class="container hidden-xs hidden-sm" role="contentinfo">
                <nav role="navigation">
                    <ul>
                        <li><a href="<?php echo url_for('contact') ?>">Contact</a></li>
                        <li><a href="<?php echo url_for('mentions_legales') ?>">Mentions légales</a></li>
                    </ul>
                </nav>
            </footer>
            <!-- end #footer -->

        </div>
        <!-- end #page -->

        <div class="alert alert-danger notification" id="ajax_form_error_notification">Une erreur est survenue</div>
        <div class="alert alert-success notification" id="ajax_form_progress_notification">Enregistrement en cours ...</div>

        <?php include_javascripts() ?>
    </body>
</html>
