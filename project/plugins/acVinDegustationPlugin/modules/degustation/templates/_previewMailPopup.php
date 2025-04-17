<div id="mailPreviewModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 class="modal-title"><?php echo $subject; ?></h3>
        <h4><span style="text-decoration: underlined dotted 1px black">Destinataire :</span> <?php echo $email ?></h4>
        <h4>Copie : <?php echo $cc ?></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12">
            <pre style="white-space: pre-wrap;"><?= include_partial('degustation/notificationEmail', array('degustation' => $degustation, 'identifiant' => $identifiant, 'lotsConformes' => $lotsConformes, 'lotsNonConformes' => $lotsNonConformes));?></pre>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div class="row">
            <div class="col-md-4 text-left">
                <a class="btn btn-default" href="<?php echo url_for('degustation_notifications_etape',array('id' => $degustation->_id)) ; ?>">Annuler</a>
            </div>
            <div class="col-md-4 text-center">
                <div class="btn-group">
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Voir les PDF <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu text-left">
                    <?php if (count($lotsConformes)): ?>
                    <li>
                        <a id="pdf_lots_conforme" href="<?php echo url_for('degustation_conformite_pdf', array('id' => $degustation->_id, 'identifiant' => $identifiant), true); ?>">
                            PDF des lots <?php echo $lotsConformes[0]->isLibelleAcceptable() ? 'acceptables' : 'conformes'; ?>
                        </a>
                    </li>
                    <?php endif ?>
                    <?php foreach ($lotsNonConformes as $lotNonConforme): ?>
                    <li>
                        <a id="pdf_lots_non_conforme" href="<?php echo url_for('degustation_non_conformite_pdf', array('id' => $degustation->_id, 'lot_dossier' => $lotNonConforme->numero_dossier, 'lot_archive' => $lotNonConforme->numero_archive), true); ?>">
                            PDF des lots non <?php echo $lotNonConforme->isLibelleAcceptable() ? 'acceptables' : 'conformes'; ?>
                        </a>
                    </li>
                    <?php endforeach ?>
                  </ul>
                </div>
            </div>
            <div class="col-md-4">
                <a class="btn btn-primary" href="<?php echo url_for('degustation_envoi_mail_resultats', array('id' => $degustation->_id, 'identifiant' => $identifiant)); ?>">
                  <i class="glyphicon glyphicon-envelope"></i>&nbsp;Envoyer par mail
                </a>
            </div>
      </div>
    </form>
  </div>
</div>
</div>
