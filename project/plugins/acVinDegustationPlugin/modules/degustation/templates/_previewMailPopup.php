<div id="mailPreviewModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><?php echo $emailLinkManager->getSubject(); ?></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12">
            <pre><?php echo $emailLinkManager->getBody(); ?></pre>
          </div>
        </div>
      <div class="modal-footer">
        <a class="btn btn-default btn pull-left" href="<?php echo url_for('degustation_notifications_etape',array('id' => $degustation->_id)) ; ?>">Annuler</a>
        <a class="btn btn-primary btn pull-right"
           href="<?php echo $emailLinkManager->getMailerLink(); ?>"
           id="link-mail-auto"
           data-retour="<?php echo url_for('degustation_envoi_mail_resultats',array('id' => $degustation->_id, 'identifiant' => $emailLinkManager->getEtablissement()->identifiant)); ?>" >Envoyer le mail</a>
      </div>
    </form>
  </div>
</div>
</div>
