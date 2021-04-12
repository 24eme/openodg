<?php use_helper("Date") ?>

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

        <link rel="shortcut icon" type="image/x-icon" href="/favico_igp13.ico" />
        <link rel="icon" type="image/x-icon" href="/favico_igp13.ico" />
        <link rel="icon" type="image/png" href="/favico_igp13.png" />

        <?php include_stylesheets() ?>
    </head>
    <body role="document">
            <div id="header">

                    <nav id="menu_navigation" class="navbar navbar-default container">
                            <div class="navbar-header">
                              <a class="navbar-brand" style="padding: 0;padding-right: 15px;"><img style="height:50px;" src="/images/logo_<?php echo sfConfig::get('sf_app') ?>.png" /></a>
                            </div>
                    </nav>
            </div>

                <section id="content" class="container">
                    <div class="page-header no-border">
                      <h2>Convocations à une dégustation</h2>
                    </div>

                    <div class="row">
                      <div class="col-xs-12">
                        <div class="panel panel-default" style="min-height: 160px">
                          <div class="panel-heading">
                            <h2 class="panel-title">
                              <div class="row">
                                <div class="col-xs-12">
                                    <?php if($presence): ?>
                                    Confirmation de votre <strong>présence</strong> à la dégustation
                                    <?php else: ?>
                                    Confirmation de votre <strong>absence</strong> à la dégustation
                                    <?php endif; ?>
                                </div>
                              </div>
                            </h2>
                          </div>
                          <div class="panel-body">
                            <?php if($presence): ?>
                            <p>Vous venez de confirmer votre venue à la dégustation qui se tiendra : </p>
                            <?php else: ?>
                            <p>Vous venez d'informer de votre absence à la dégustation qui se tiendra : </p>
                            <?php endif; ?>
                            <h3 class="text-center">
                            <small><?php echo $degustation->getLieuNom(); ?></small><br/>
                            le <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?>
                            </h3>
                            <br/>
                            <?php if($presence): ?>
                                <p>Si toutefois, il s'agit d'une erreur et que vous ne pouvez pas venir à cette dégustation veuillez cliquer ci-dessous : </p>
                            <?php else: ?>
                                <p>Si toutefois, il s'agit d'une erreur et que vous désirez confirmer votre présence à cette dégustation veuillez cliquer ci-dessous : </p>
                            <?php endif; ?>
                                    <a href="<?php echo url_for('degustation_convocation_auth', [
                                        'id' => $degustation->_id,
                                        'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
                                        'college' => $college,
                                        'identifiant' => $identifiant,
                                        'presence' => intval(!$presence)
                                    ], true) ?>" class="btn pull-right <?php if($presence): ?>btn-default<?php else: ?>btn-success<?php endif; ?>">
                                    <?php if($presence): ?>
                                    <span class="glyphicon glyphicon-remove"></span>&nbsp;Je ne viens pas à cette dégustation
                                    <?php else: ?>
                                    <span class="glyphicon glyphicon-check"></span>&nbsp;Je viens à cette dégustation
                                    <?php endif; ?>
                                    </a>
                        </div>
                        </div>
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
