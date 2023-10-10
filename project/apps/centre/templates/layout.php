<?php $gitcommit = file_exists('../../.git/ORIG_HEAD') ? str_replace("\n", "", file_get_contents('../../.git/ORIG_HEAD')) : null;?>
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

        <link rel="shortcut icon" type="image/x-icon" href="/favico_centre.ico" />
        <link rel="icon" type="image/x-icon" href="/favico_centre.ico" />
        <link rel="icon" type="image/png" href="/favico_centre.png" />

        <link href="<?php echo public_path("/components/vins/vins.css").'?'.$gitcommit; ?>" rel="stylesheet">
        <link href="<?php echo public_path("/css/compile_centre.css").'?'.$gitcommit; ?>" rel="stylesheet">
        <link href="<?php echo public_path("/js/lib/leaflet/leaflet.css").'?'.$gitcommit; ?>" rel="stylesheet">
        <link href="/css/style_centreloire.css" rel="stylesheet" type="text/css">

        <?php include_stylesheets() ?>

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,600" rel="stylesheet" type="text/css">

        <script type="text/javascript" src="/js/lib/modernizr-2.8.2.js"></script>
        <script type="text/javascript" src="/js/lib/device.min.js"></script>

        <!--[if lt IE 9]>
            <script type="text/javascript" src="/js/lib/respond.min.js"></script>
        <![endif]-->
    </head>
    <body role="document" style="background-image: url('data:image/jpg;base64,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'); background-size: 100%;">

        <!-- ####### PLEASE KEEP ####### -->
        <!--[if lte IE 7 ]>
        <div id="message_ie">
            <div class="gabarit">
                <p><strong>Vous utilisez un navigateur obsolète depuis près de 10 ans !</strong> Il est possible que l'affichage du site soit fortement altéré par l'utilisation de celui-ci.</p>
            </div>
        </div>
        <![endif]-->
        <!-- ####### PLEASE KEEP ####### -->

            <div id="header">
                <?php echo include_partial('global/header'); ?>

                <?php include_partial('global/nav'); ?>
            </div>

                <section id="content" class="container">
                        <?php if(sfConfig::get('app_instance') == 'preprod' ): ?>
                          <div><p style="color:red; text-align:center; font-weight: bold;">Preproduction (la base est succeptible d'être supprimée à tout moment)</p></div>
                        <?php endif; ?>

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

            <div class="alert alert-danger notification" id="ajax_form_error_notification">Une erreur est survenue</div>
            <div class="alert alert-success notification" id="ajax_form_progress_notification">Enregistrement en cours ...</div>
            <?php include_javascripts() ?>
    </body>
</html>
