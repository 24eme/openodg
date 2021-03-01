<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>

<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_NOTIFICATIONS)); ?>

<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<div class="page-header no-border">
  <h2>Notifications pour les opérateurs</h2>
  <h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?> <small><?php echo $degustation->getLieuNom(); ?></small></h3>
</div>
<div class="row row-condensed">
  <div class="col-xs-12">
    <div class="panel panel-default">
      <div class="panel-body">

        <div class="row row-condensed">
          <div class="col-xs-12">
            <h3>Échantillons par opérateurs</h3>
            <table class="table table-bordered table-condensed">
              <thead>
                <tr>
                  <th class="col-xs-4 text-left">Opérateur</th>
                  <th class="col-xs-4 text-left">Echantillons dégustés</th>
                  <th class="col-xs-2 text-left">Notifications</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($degustation->getLotsByOperateursAndConformites() as $conformitesLots): ?>
                <tr class="vertical-center">
                  <td class="text-left">
                    <?php echo $conformitesLots->declarant_nom; ?>
                  </td>
                  <td class="text-left">
                    <?php foreach ($conformitesLots->lots as $conformite => $lots): ?>
                      <?php foreach ($lots as $lot): ?>
                        <a data-toggle="tooltip" title='<?php echo $lot->produit_libelle;?>&nbsp;
                          <?php echo showProduitLot($lot); ?>
                          <?php if($lot->isNonConforme() || $lot->isConformeObs()): ?>
                            <?php echo "&nbsp;&nbsp;".$lot->getShortLibelleConformite(); ?>
                          <?php endif; ?>
                          '
                          class="label <?php if($lot->isNonConforme()): ?>label-danger<?php elseif($lot->isConformeObs()): ?>label-warning<?php else: ?>label-success<?php endif; ?>"><span class="glyphicon <?php if($lot->isNonConforme()): ?>glyphicon-remove<?php else: ?>glyphicon-ok<?php endif ?>"></span></a>&nbsp;
                          <?php endforeach; ?>
                        <?php endforeach; ?>
                      </td>
                      <td class="text-center">
                        <a href="<?php echo url_for('degustation_mail_resultats_previsualisation',array('id' => $degustation->_id, 'identifiant' => $lot->declarant_identifiant)); ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-envelope"></i></a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <div class="row row-margin row-button">
                <div class="col-xs-4"><a href="<?php echo url_for("degustation_resultats_etape", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
                <div class="col-xs-4 text-center">
                </div>
                <div class="col-xs-4 text-right">
                  <a href="<?php echo url_for("degustation") ?>" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php
  if(isset($emailLinkManager)):
    include_partial('degustation/previewMailPopup', array('emailLinkManager' => $emailLinkManager, 'degustation' => $degustation));
 endif;
  ?>
