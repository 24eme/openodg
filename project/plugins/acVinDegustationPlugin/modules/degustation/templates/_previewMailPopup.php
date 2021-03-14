<div id="mailPreviewModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><?php echo $subject; ?></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12">
            <pre><?= include_partial('degustation/notificationEmail', array('degustation' => $degustation, 'identifiant' => $identifiant, 'lotsConformes' => $lotsConformes, 'lotsNonConformes' => $lotsNonConformes));?></pre>
          </div>
        </div>
      <div class="modal-footer">
        <a class="btn btn-default btn pull-left" href="<?php echo url_for('degustation_notifications_etape',array('id' => $degustation->_id)) ; ?>">Annuler</a>
        <?php include_component('degustation', 'mailTo', ['degustation' => $degustation, 'identifiant' => $identifiant, 'lots' => $lots[$identifiant]]) ?>
      </div>
    </form>
  </div>
</div>
</div>
