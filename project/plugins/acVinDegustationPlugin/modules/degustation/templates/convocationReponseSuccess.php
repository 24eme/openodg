<?php $gitcommit = file_exists('../../.git/ORIG_HEAD') ? str_replace("\n", "", file_get_contents('../../.git/ORIG_HEAD')) : null;?>
<?php use_helper("Date") ?>

<!doctype html>
<head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>

    <link rel="shortcut icon" type="image/x-icon" href="/favico_igp13.ico" />
    <link rel="icon" type="image/x-icon" href="/favico_igp13.ico" />
    <link rel="icon" type="image/png" href="/favico_igp13.png" />

    <link href="<?php echo public_path("/css/compile_default.css").'?'.$gitcommit; ?>" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,600" rel="stylesheet" type="text/css">
    <link href="/css/style_igp.css" rel="stylesheet" type="text/css">
</head>

<body role="document">
    <div id="header">
        <nav id="menu_navigation" class="navbar navbar-default container">
            <div class="navbar-header">
              <a class="navbar-brand" style="padding: 0;padding-right: 15px;"><img style="height:50px;" src="/images/logo_<?php echo sfConfig::get('sf_app') === 'gaillac' ? 'aop'.sfConfig::get('sf_app') : sfConfig::get('sf_app'); ?>.png" /></a>
            </div>
        </nav>
    </div>

    <section id="content" class="container">
        <div class="page-header no-border">
          <h2>Convocation à une dégustation</h2>
        </div>

        <p>
            Vous venez d'être convoqué à la dégustation suivante :
        </p>

        <p class="well">
            Dégustation du <strong><?php echo format_date($degustation->date, "P", 'fr_FR') ?> à <?php echo format_date($degustation->date, "H'h'mm", 'fr_FR') ?></strong></br>
            qui se tiendra au <?php echo $degustation->getLieuNom() ?>
        </p>

        <div class="row">
            <p class="text-center">
                Merci de choisir une des deux options :
            </p>
            <div class="col-xs-6 text-right">
            <a href="<?php echo url_for('degustation_convocation_auth', [
                'id' => $degustation->_id,
                'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
                'college' => $college,
                'identifiant' => $identifiant
            ], true) ?>" class="btn btn-success">
                    <i class="glyphicon glyphicon-ok"></i>
                    Je serai présent
                </a>
            </div>

            <div class="col-xs-6">
            <a href="<?php echo url_for('degustation_convocation_auth', [
                'id' => $degustation->_id,
                'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
                'college' => $college,
                'identifiant' => $identifiant,
                'presence' => 0
            ], true) ?>" class="btn btn-danger">
                    <i class="glyphicon glyphicon-remove"></i>
                    Je serai absent
                </a>
            </div>
        </div>
    </section>

    <footer id="footer" class="container hidden-xs hidden-sm text-center mt-5 mb-5" role="contentinfo">
        <nav role="navigation">
            <ul class="list-inline" style="font-size: 13px;">
            </ul>
        </nav>
    </footer>
</body>
</html>
