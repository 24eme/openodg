<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li><a href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $date_tournee, 'agent_identifiant' => $agent_identifiant)) ?>">Tournée du <?php echo Date::francizeDate($date_tournee); ?></a></li>
  <li class="active"><a href="">Liste des opérateurs contrôlés</a></li>
</ol>

<h2>Opérateurs controlés dans la tournée du <?php echo Date::francizeDate($date_tournee); ?></h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-xs-4">Opérateurs</th>
        <th class="col-xs-2">Type de tournée</th>
        <th class="col-xs-6" colSpan="3">Documents</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($controles as $controle): ?>
        <tr>
            <td>
                <a href="<?php echo url_for("controle_operateur", array('identifiant' => $controle->identifiant)) ?>"><?php echo $controle->declarant->raison_sociale ?></a>
            </td>
            <td><?php echo $controle->type_tournee; ?></td>
            <td>
                <a href="<?php echo url_for('controle_pdf', array('id' => $controle->_id)); ?>">PDF du contrôle</a>
            </td>
            <td>
                <?php if ($controle->needConstatsToBeCreated()): ?>
                    <a class="btn btn-xs btn-default" href="<?php echo url_for('controle_liste_manquements_controle', array('id' => $controle->_id)); ?>">Générer les manquements</a>
                <?php else: ?>
                    <a href="<?php echo url_for('controle_pdf_manquements', array('id' => $controle->_id)); ?>">PDF des manquements</a>
                    <?php if ($controle->isANotifier()) :?>
                    <a class="btn btn-xs btn-default" href="<?php echo url_for('controle_liste_manquements_controle', array('id' => $controle->_id)); ?>"><span class="glyphicon glyphicon-pencil"></span></a>
                    <?php endif; ?>
                <?php endif;?>
            </td>
            <td>
            <?php if ($controle->notification_date === null): ?>
                <div class="btn-group pull-right">
                  <button type="button" class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"<?php if (!$controle->manquements_valides):?> title="Les manquements doivent être générés afin de pouvoir notifier l'opérateur" disabled<?php endif;?>>
                    Notifier <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu text-left">
                    <li>
                        <a class="btn link-mail-auto" href="<?php echo url_for('controle_envoi_mail_resultats', array('id_controle' => $controle->_id, 'identifiant' => $controle->identifiant)); ?>">
                          <i class="glyphicon glyphicon-envelope"></i>&nbsp;Envoyer par mail
                        </a>
                    </li>
                    <li>
                      <a href="<?php echo url_for('controle_mail_resultats_previsualisation', array('id_controle' => $controle->_id)); ?>" class="btn btn-mail-previsualisation">
                          <i class="glyphicon glyphicon-eye-open"></i>&nbsp;Prévisualiser
                      </a>
                    </li>
                  </ul>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <a href="<?php echo url_for('controle_mail_resultats_previsualisation',array('id_controle' => $controle->_id)); ?>" class="btn btn-default btn-sm disabled">
                        <i class="glyphicon glyphicon-send"></i>&nbsp;&nbsp;<?php echo format_date($controle->notification_date, "dd/MM/yyyy")." à ".format_date($controle->notification_date, "H")."h".format_date($controle->notification_date, "mm"); ?>
                    </a>
                    <br/><a href="<?php echo url_for('controle_envoi_mail_resultats',array('id_controle' => $controle->_id, 'identifiant' => $controle->identifiant,'envoye' => 0)); ?>" ><small>Remettre en non envoyé</small></a>
                </div>
            <?php endif ?>
          </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="row">
    <div class="col-xs-4"><a class="btn btn-default" href="<?php echo url_for('controle_index'); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
</div>


<?php
if(isset($popup)):
  include_component('controle','previewMailPopup', array('controle' => $controle));
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
           <a href="<?php echo url_for('controle_mail_to_resultats', array('id_controle' => $controle->_id, 'identifiant' => $controle->identifiant)); ?>" class="btn btn-primary">Ouvrir le mail manuellement</a>
         </div>
       </div>
     </div>
   </div>
<script>
    var newWin = window.open("<?php echo url_for('controle_mail_to_resultats', array('id_controle' => $controle->_id, 'identifiant' => $controle->identifiant)); ?>");
    if(!newWin || newWin.closed || typeof newWin.closed=='undefined')
    {
       setTimeout(function() {$('#modal_mailto').modal('show')}, 1000);
    }
</script>

<?php endif ?>
