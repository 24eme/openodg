<div id="mailPreviewModal" class="modal modal-auto-open" tabindex="-1" role="dialog">
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
            <pre style="white-space: pre-wrap;"><?= include_partial('controle/notificationEmail', array('controle' => $controle, 'agent' => CompteClient::getInstance()->find($controle->agent_identifiant)));?></pre>
          </div>
        </div>
      </div>
      <div class="modal-footer">
          <div class="row">
              <div class="col-md-4 text-left">
                  <a class="btn btn-default" href="<?php echo url_for('controle_liste_operateur_tournee',array('date' => $controle->date_tournee, 'agent_identifiant' => $controle->agent_identifiant)) ; ?>">Annuler</a>
              </div>
              <div class="col-md-4 text-center">
                  <div class="btn-group">
                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Voir les PDF <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu text-left">
                          <li>
                              <a href="<?php echo url_for('controle_pdf', array('id' => $controle->_id)); ?>">PDF du contrôle</a>
                          </li>
                          <li>
                              <a href="<?php echo url_for('controle_pdf_manquements', array('id' => $controle->_id)); ?>">PDF des manquements</a>
                          </li>
                      </ul>
                  </div>
              </div>
              <div class="col-md-4">
                  <a class="btn btn-primary" href="<?php echo url_for('controle_envoi_mail_resultats', array('id_controle' => $controle->_id, 'identifiant' => $controle->identifiant)); ?>">
                      <i class="glyphicon glyphicon-envelope"></i>&nbsp;Envoyer par mail
                  </a>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>
