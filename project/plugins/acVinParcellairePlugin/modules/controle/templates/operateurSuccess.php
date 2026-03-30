<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li><a href="<?php echo url_for('controle_operateur', $etablissement); ?>"><?php echo $etablissement->raison_sociale; ?> (<?php echo $etablissement->identifiant ?> - <?php echo $controle->declarant->cvi ?>)</a></li>
</ol>

<h2 class="mb-4">Les contrôles de <?php echo $etablissement->raison_sociale; ?></h2>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $etablissement]); ?>
</div>

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
        <a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm"><span class="glyphicon glyphicon-edit"></span></a>
    </th>
    <td class="text-center"><?php echo count($controle->parcelles); ?> / <?php echo count($controle->manquements); ?></td>
    <?php if (!$controle->isPlanifie()): ?>
        <td colspan="3"><a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-time"></span> Planifier le controle</a></td>
    <?php else: ?>
    <td></td>
    <td><a href="<?php echo url_for('controle_apporga', array('date' => $controle->date_tournee, 'agent_identifiant' => $controle->agent_identifiant)); ?>#/<?php echo $controle->_id; ?>" class="btn btn-sm <?php if($controle->getStatutComputed() == ControleClient::CONTROLE_STATUT_A_ORGANISER): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-th-list"></span> Préparer    </a></td>
    <td><a href="<?php echo url_for('controle_appterrain', array('date' => $controle->date_tournee, 'agent_identifiant' => $controle->agent_identifiant)); ?>#/<?php echo $controle->_id; ?>" class="btn btn-sm <?php if($controle->getStatutComputed() == ControleClient::CONTROLE_STATUT_ORGANISE): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-road"></span> Tournée</a></td>
    <?php endif; ?>
    <td><a href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $controle->date_tournee, 'agent_identifiant' => $controle->agent_identifiant)); ?>" class="btn btn-sm <?php if($controle->getStatutComputed() == ControleClient::CONTROLE_STATUT_EN_MANQUEMENT): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-cog"></span> Manquements</a></td>
    <td>
        <small>
        <?php if ($controle->isControle()):?>
        <a href="<?php echo url_for('controle_pdf', array('id' => $controle->_id)); ?>">contrôle</a><br/>
        <?php endif; ?>
        <?php if ($controle->isTermine()): ?>
        <a href="<?php echo url_for('controle_pdf_manquements', array('id' => $controle->_id)); ?>">manquements</a>
        <?php endif; ?>
        </small>
    </td>
    </tr>
<?php endforeach; ?>
</table>
</div>
