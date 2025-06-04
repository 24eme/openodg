<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>

<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_NOTIFICATIONS)); ?>

<div class="page-header no-border">
  <h2>Notifications pour les opérateurs</h2>
  <a class="pull-right btn btn-link btn-default" href="<?= url_for('degustation_export_csv', ['id' => $degustation->_id]) ?>"><i class="glyphicon glyphicon-export"></i> Export CSV</a>
  <h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?> <small><?php echo $degustation->getLieuNom(); ?></small></h3>
</div>
<div class="row row-condensed">
  <div class="col-xs-12">
    <div class="panel panel-default">
      <div class="panel-body">

        <div class="row row-condensed">
          <div class="col-xs-4">
              <a id="btn_pdf_fiches_proces_verbal" class="btn btn-default" href="<?php echo url_for('degustation_proces_verbal_degustation_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche de procès verbal</a>
          </div>
          <div class="col-xs-4 col-xs-offset-4">
              <a id="btn_pdf_notifications" class="pull-right btn btn-success" href="<?= url_for('degustation_all_notification_pdf', ['id' => $degustation->_id]) ?>"><i class="glyphicon glyphicon-file"></i> Télécharger toutes les notifications</a>
          </div>
          <div class="col-xs-12">
          <h3>Échantillons par opérateurs</h3>
            <table class="table table-bordered table-condensed">
              <thead>
                <tr>
                  <th class="col-xs-3 text-left">Opérateur</th>
                  <th class="col-xs-5 text-left">Echantillons dégustés</th>
                  <th class="col-xs-2 text-left">Notifications</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($degustation->getLotsByOperateurs() as $identifiant => $lots): ?>
                    <tr>
                      <td><?= $lots[0]->declarant_nom ?></td>
                      <td style="line-height: 2.5rem">
                        <?php foreach ($lots as $lot): ?>
                        <?php if($lot->hasSpecificitePassage()): ?>
                        <span class="label label-danger" style="margin-right: -14px;">&nbsp;</span>
                        <?php endif; ?>
                        <a href="<?php  echo url_for('degustation_lot_historique', array('identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id));  ?>" data-toggle="tooltip"
                              data-html="true"
                              title="<?= $lot->getLibelle() . ' - ' . $lot->volume . "hl<br>" . $lot->getShortLibelleConformite() ?>"
                              class="label label-<?php if($lot->isManquement())  { echo 'danger'; }
                                                    elseif ($lot->isConformeObs()) { echo 'warning'; }
                                                    else { echo 'success'; } ?>"
                              style="<?php if($lot->hasSpecificitePassage()): ?>border-radius: 0 0.25em 0.25em 0; border-left: 1px solid #fff;<?php endif; ?>"
                        ><span class="glyphicon glyphicon-<?= ($lot->isManquement()) ? 'remove' : 'ok' ?>"></span></a>&nbsp;
                        <?php endforeach; ?>
                      </td>
                        <?php ?>
                        <td>
                          <a class="pull-right" title="Ouvrir le mail" style="color: white;" href="<?php echo url_for('degustation_mail_to_resultats', array('id' => $degustation->_id, 'identifiant' => $lot->declarant_identifiant)); ?>"><span class="glyphicon glyphicon-envelope"></span></a>
                        <?php if ($lot->email_envoye === null): ?>
                            <div class="btn-group">
                              <button type="button" class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Notifier <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu text-left">
                                <li>
                                    <a class="btn link-mail-auto" href="<?php echo url_for('degustation_envoi_mail_resultats', array('id' => $degustation->_id, 'identifiant' => $identifiant)); ?>">
                                      <i class="glyphicon glyphicon-envelope"></i>&nbsp;Envoyer par mail
                                    </a>
                                </li>
                                <li>
                                  <a href="<?php echo url_for('degustation_mail_resultats_previsualisation', array('id' => $degustation->_id, 'identifiant' => $identifiant)); ?>" class="btn btn-mail-previsualisation">
                                      <i class="glyphicon glyphicon-eye-open"></i>&nbsp;Prévisualiser
                                  </a>
                                </li>
                              </ul>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo url_for('degustation_mail_resultats_previsualisation',array('id' => $degustation->_id, 'identifiant' => $identifiant)); ?>" class="btn btn-default btn-sm disabled">
                                <i class="glyphicon glyphicon-send"></i>&nbsp;&nbsp;<?php echo format_date($lot->email_envoye, "dd/MM/yyyy")." à ".format_date($lot->email_envoye, "H")."h".format_date($lot->email_envoye, "mm"); ?>
                            </a>
                          <br/><a href="<?php echo url_for('degustation_envoi_mail_resultats',array('id' => $degustation->_id, 'identifiant' => $lot->declarant_identifiant,'envoye' => 0)); ?>" ><small>Remettre en non envoyé</small></a>
                        <?php endif ?>
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
                    <a href="<?php echo url_for("degustation_cloture", $degustation) ?>" class="btn btn-primary btn-upper">Cloturer la dégustation <span class="glyphicon glyphicon-chevron-right"></span></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php
  if(isset($popup)):
    include_component('degustation','previewMailPopup', array('degustation' => $degustation, 'identifiant' => $identifiant_operateur, 'lots' => $lotsOperateur));
 endif;
  ?>

 <?php if (isset($mail_to_identifiant) && $mail_to_identifiant): ?>
     <div id="modal_mailto" class="modal fade" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Le mail n'a pas pu s'ouvrir automatiquement</h4>
          </div>
          <div class="modal-body">
              <span class="glyphicon glyphicon-info-sign"></span> Vous devez autoriser le navigateur à ouvrir des popups pour activer l'ouverture automatique. (<a href="https://github.com/24eme/openodg/blob/master/doc/AutorisationPopup.md">Consulter l'aide</a>)</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Annuler</button>
            <a href="<?php echo url_for('degustation_mail_to_resultats', array('id' => $degustation->_id, 'identifiant' => $mail_to_identifiant)); ?>" class="btn btn-primary">Ouvrir le mail manuellement</a>
          </div>
        </div>
      </div>
    </div>
 <script>
     var newWin = window.open("<?php echo url_for('degustation_mail_to_resultats', array('id' => $degustation->_id, 'identifiant' => $mail_to_identifiant)); ?>");
     if(!newWin || newWin.closed || typeof newWin.closed=='undefined')
     {
        setTimeout(function() {$('#modal_mailto').modal('show')}, 1000);
     }
 </script>

 <?php endif ?>
