<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li><a href="<?php echo url_for('controle_operateur', $etablissement); ?>"><?php echo $etablissement->raison_sociale; ?> (<?php echo $etablissement->identifiant ?> - <?php echo $controle->declarant->cvi ?>)</a></li>
</ol>

<?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('controle_etablissement_selection'), 'noautofocus' => true)); ?>


<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $etablissement]); ?>
</div>

<h2 class="mb-4">Historique de ses contrôles</h2>
<div>
<table class="table">
    <tr>
        <th>Campagne</th>
        <th>Type</th>
        <th>Date</th>
        <th class="text-center">Parcelles / Manquements</th>
        <th></th>
        <th>Organisation</th>
        <th>Controles</th>
        <th>Manquements</th>
        <th>PDF</th>
    <tr>
<?php foreach($controles as $controle): ?>
    <tr>
    <th><?php echo $controle->campagne; ?></th>
    <th><?php echo $controle->type_tournee; ?></th>
    <th>
        <?php if ($controle->date_tournee): ?><?php echo Date::francizeDate($controle->date_tournee); ?><?php else:?>A venir<?php endif; ?>
        <?php  if (! $controle->isControleCloture()): ?>
            <a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm"><span class="glyphicon glyphicon-edit"></span></a>
        <?php endif;?>
    </th>
    <td class="text-center"><?php echo count($controle->parcelles); ?> / <?php echo count($controle->manquements); ?></td>
    <?php  if (! $controle->isControleCloture()): ?>
        <?php if (!$controle->isPlanifie()): ?>
            <td colspan="3"><a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-time"></span> Planifier le controle</a></td>
        <?php else: ?>
            <td></td>
            <td><a href="<?php echo url_for('controle_apporga', array('date' => $controle->date_tournee, 'agent_identifiant' => $controle->agent_identifiant)); ?>#/<?php echo $controle->_id; ?>" class="btn btn-sm <?php if($controle->getStatutComputed() == ControleClient::CONTROLE_STATUT_A_ORGANISER): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-th-list"></span> Préparer    </a></td>
            <td><a href="<?php echo url_for('controle_appterrain', array('date' => $controle->date_tournee, 'agent_identifiant' => $controle->agent_identifiant)); ?>#/<?php echo $controle->_id; ?>" class="btn btn-sm <?php if($controle->getStatutComputed() == ControleClient::CONTROLE_STATUT_ORGANISE): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-road"></span> Tournée</a></td>
        <?php endif; ?>
        <td><a href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $controle->date_tournee, 'agent_identifiant' => $controle->agent_identifiant)); ?>" class="btn btn-sm <?php if($controle->getStatutComputed() == ControleClient::CONTROLE_STATUT_TOURNEE_TERMINEE_AVEC_MANQUEMENTS_A_TRAITER): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-cog"></span> Manquements</a></td>
    <?php else: ?>
        <th colspan="4" class="text-center">Contrôle cloturé</th>
    <?php endif;?>
    <td>
        <small>
        <?php if ($controle->isControle()):?>
        <a href="<?php echo url_for('controle_pdf', array('id' => $controle->_id)); ?>">contrôle</a><br/>
        <?php endif; ?>
        <?php if ($controle->isTourneeTerminee()): ?>
        <a href="<?php echo url_for('controle_pdf_manquements', array('id' => $controle->_id)); ?>">manquements</a>
        <?php endif; ?>
        </small>
    </td>
    </tr>
<?php endforeach; ?>
</table>

<h2 class="mb-4">Les manquements en cours</h2>
<div>
<table class="table">
    <tr>
        <th>Date de controle</th>
        <th>Date de notification</th>
        <th>Manquement</th>
        <th class="text-center">Délais</th>
        <th></th>
    </tr>
<?php foreach($manquements as $controle_manquements): foreach($controle_manquements->manquements as $manquement): if ($manquement && $manquement->actif): ?>
    <tr>
        <td><?php echo Date::francizeDate($controle_manquements->date_tournee); ?></td>
        <td><?php echo Date::francizeDate($manquement->notification_date); ?></td>
        <td><?php echo $manquement->libelle_manquement; ?></td>
        <td class="text-center"><?php echo $manquement->delais; ?></td>
        <td><a class="btn" href="<?php echo url_for('controle_liste_manquements_operateur', ['id_controle' => $controle_manquements->_id]); ?>">traiter</a></td>
    </tr>
<?php endif; endforeach; endforeach; ?>
</table>
</div>
<hr/>
<div class="row">
    <div class="dropdown center-block" style="width: 250px;">
      <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        Enregistrer un nouveau controle
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        <?php foreach(ControleClient::getInstance()->getTypes() as $type): ?>
        <li><a href="<?php echo url_for('controle_nouveau', $etablissement); ?>?type=<?php echo $type; ?>"><?php echo $type; ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
</div>
</div>
