<a class="btn" href="<?= $mailto ?>"
  class="link-mail-auto"
  data-retour="<?php echo url_for('degustation_envoi_mail_resultats', array('id' => $degustation->_id, 'identifiant' => $identifiant)); ?>">
  <i class="glyphicon glyphicon-envelope"></i>&nbsp;Envoyer par mail
</a>

