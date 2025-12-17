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
        <link href="/css/style_provence.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" type="text/css" media="screen" href="/js/lib/leaflet/leaflet.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="/js/lib/leaflet/marker.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="/js/lib/leaflet-gps/dist/leaflet-gps.min.css" />

        <script src="/js/lib/vue/vue.global.prod.js"></script>
        <script src="/js/lib/vue/vue-router.global.prod.js"></script>
        <script src="/js/lib/idb/build/umd.js"></script>
        <script src="/js/lib/leaflet/leaflet.js"></script>
        <script src="/js/lib/leaflet-gps/dist/leaflet-gps.min.js"></script>
        <script src="/js/lib/signature_pad.min.js"></script>
        <!--<script src="/js/lib/leaflet-offline/dist/bundle.js"></script>-->
    </head>
    <body role="document" style="background: none;">

        <div id="header">
            <!--<header class="container" role="banner" style="background:none;">
                <div id="logo">
                  <a href="/" title="Plateforme des Syndicats des Vins de Provence | Retour à la page d'accueil">
                      <img src="/images/logo_provence.png?20241029" alt="Syndicats des Vins de Provence">
                  </a>
              </div>
              <div id="titre">
                  <h1 style="color:red; text-align:center;">VUEJS APP CONTROLE TERRAIN</h1>
              </div>
            </header>-->
        </div>

        <section id="content" class="container">
            <?php echo $sf_content ?>
        </section>
    </body>
</html>
