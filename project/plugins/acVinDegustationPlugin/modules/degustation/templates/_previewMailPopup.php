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
            <pre><?= include_partial('degustation/notificationEmail', array('degustation' => $degustation, 'identifiant' => $identifiant, 'lotsConformes' => $lotsConformes, 'lotsNonConformes' => $lotsNonConformes));?></pre>
          </div>
        </div>
      <div class="modal-footer">
        <div class="btn-group">
          <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions <span class="caret"></span>
          </button>
          <ul class="dropdown-menu text-left">
            <li>
                <?php include_component('degustation', 'mailTo', ['degustation' => $degustation, 'identifiant' => $identifiant, 'lots' => $lots[$identifiant]]) ?>
            </li>
            <?php if (count($lotsConformes)): ?>
            <li>
                <a href="<?php echo url_for('degustation_conformite_pdf', array('id' => $degustation->_id, 'identifiant' => $identifiant), true); ?>">
                    PDF des lots conformes
                </a>
            </li>
            <?php endif ?>
            <?php foreach ($lotsNonConformes as $lotNonConforme): ?>
            <li>
                <a href="<?php echo url_for('degustation_non_conformite_pdf', array('id' => $degustation->_id, 'lot_dossier' => $lotNonConforme->numero_dossier, 'lot_archive' => $lotNonConforme->numero_archive), true); ?>">
                    PDF des lots non conformes
                </a>
            </li>
            <?php endforeach ?>
          </ul>
        </div>

        <a class="btn btn-default btn pull-left" href="<?php echo url_for('degustation_notifications_etape',array('id' => $degustation->_id)) ; ?>">Annuler</a>
      </div>
    </form>
  </div>
</div>
</div>
