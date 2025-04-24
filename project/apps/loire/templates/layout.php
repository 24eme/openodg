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

        <link rel="shortcut icon" type="image/x-icon" href="https://authentification.vinsvaldeloire.pro/cas/favicon.ico" />
        <link rel="icon" type="image/x-icon" href="https://authentification.vinsvaldeloire.pro/cas/favicon.ico" />

        <?php include_stylesheets() ?>

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,600" rel="stylesheet" type="text/css">
        <link href="<?php echo public_path("/css/style_loire.css?201801171851") ?>" rel="stylesheet" type="text/css">

        <script type="text/javascript" src="<?php echo public_path("/js/lib/modernizr-2.8.2.js") ?>"></script>
        <script type="text/javascript" src="<?php echo public_path("/js/lib/device.min.js") ?>"></script>

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

        <?php $route = ($sf_request->getAttribute('sf_route')) ? $sf_request->getAttribute('sf_route')->getRawValue() : NULL; ?>
        <?php $etablissement = null ?>
        <?php $compte = null; ?>

        <?php if($route instanceof EtablissementRoute): ?>
            <?php $etablissement = $route->getEtablissement(); ?>
        <?php endif; ?>
        <?php if($route instanceof SocieteRoute): ?>
            <?php $etablissement = $route->getEtablissement(); ?>
        <?php endif; ?>

        <?php if($sf_user->isAuthenticated() && !($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN) || $sf_user->hasDrevAdmin()) && (!$compte || !$etablissement)): ?>
            <?php $compte = $sf_user->getCompte(); ?>
            <?php $etablissement = ($compte->getSociete()) ? $compte->getSociete()->getEtablissementPrincipal() : null; ?>
        <?php endif; ?>
	<?php if($sf_user->isAuthenticated() && $sf_user->hasDrevAdmin() && !$compte): ?>
		<?php $compte = $sf_user->getCompte(); ?>
	<?php endif; ?>

            <?php if(sfConfig::get('app_url_header')): ?>
            <?php echo file_get_contents(sfConfig::get('app_url_header')."?compte_id=".(($compte) ? $compte->_id : "")."&etablissement_id=".(($etablissement) ? $etablissement->_id : "")."&usurpation=".(($sf_user->isUsurpationCompte()) ? "1" : "0")."&actif=".(($route instanceof InterfaceDeclarationRoute) ? 'drev' : null)); ?>
            <?php else: ?>
            <?php include_partial('global/header'); ?>
            <?php include_partial('global/nav'); ?>
            <?php endif; ?>

            <section id="content" style="position: relative;" class="container">
                <?php if(sfConfig::get('app_instance') == 'preprod' ): ?>
                  <div><p style="color:red; text-align:center; font-weight: bold;">Preproduction (la base est susceptible d'être supprimée à tout moment)</p></div>
                <?php endif; ?>

                <?php if ($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN) && $compte && $route instanceof InterfaceUsurpationRoute && !$sf_user->isUsurpationCompte()) : ?>
                     <a tabindex="-1" style="position: absolute; right:20px;" href="<?php echo url_for('auth_usurpation', array('identifiant' => $compte->identifiant)) ?>" title="Connexion mode déclarant"><span class="glyphicon glyphicon-cloud-upload"></span></a>
                <?php endif; ?>

                <?php if ($etablissement && $route instanceof InterfaceDeclarationRoute ) : ?>
                  <?php $drev = DrevClient::getInstance()->getLastDrevFromEtablissement($etablissement); ?>

                <?php if($drev): ?>
                    <?php include_partial('drev/popupSyndicats',array('drev' => $drev)); ?>
                <?php endif; ?>
              <?php endif; ?>

                <?php if ($sf_user->isUsurpationCompte()): ?>
                    <a tabindex="-1" style="position: absolute; right:20px;" href="<?php echo url_for('auth_deconnexion_usurpation') ?>" title="Déconnexion du mode déclarant"><span class="glyphicon glyphicon-cloud-download"></span></a>
                <?php endif; ?>

                <?php echo $sf_content ?>
            </section>

            <footer id="footer" class="container hidden-xs hidden-sm text-center" role="contentinfo" style="margin-top: 40px;">
                <nav role="navigation">
                    <ul class="list-inline" style="font-size: 13px;">
                        <li><a href="https://www.vinsvaldeloire.fr/fr/mentions">Mentions légales</a></li>
                    </ul>
                </nav>
            </footer>
            <!-- end #footer -->

        <!-- end #page -->

        <div class="alert alert-danger notification" id="ajax_form_error_notification">Une erreur est survenue</div>
        <div class="alert alert-success notification" id="ajax_form_progress_notification">Enregistrement en cours ...</div>
        <?php include_javascripts() ?>
    </body>
</html>
