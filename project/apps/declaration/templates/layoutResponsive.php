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
        
        <link rel="shortcut icon" type="image/x-icon" href="/favico.ico" />
        <link rel="icon" type="image/x-icon" href="/favico.ico" />
        <link rel="icon" type="image/png" href="/favico.png" />

        <?php include_stylesheets() ?>
        
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,600" rel="stylesheet" type="text/css">

        <!--<script type="text/javascript" src="/js/lib/modernizr-2.8.2.js"></script>-->
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
            
            <!-- #content -->
            <section class="container-fluid">
                <?php echo $sf_content ?>
            </section>
        </div>
        <!-- end #page -->

        <div class="alert alert-danger notification" id="ajax_form_error_notification">Une erreur est survenue</div>
        <div class="alert alert-success notification" id="ajax_form_progress_notification">Enregistrement en cours ...</div>

        <?php include_javascripts() ?>
    </body>
</html>
