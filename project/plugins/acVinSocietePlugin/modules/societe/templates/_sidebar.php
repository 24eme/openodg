<div class="panel panel-default" style="margin-bottom: 10px;">
    <div class="panel-heading"><h3 class="panel-title">Société</h3></div>
    <div class="list-group">
        <div class="list-group-item clearfix <?php if($societe->_id == $activeObject->_id): ?>active-bordered<?php endif; ?>">
            <?php include_partial('societe/bloc', array('societe' => $societe)); ?>
        </div>
    </div>
</div>
<div class="panel panel-default" style="margin-bottom: 10px;">
    <div class="panel-heading"><h3 class="panel-title">Établissements</h3></div>
    <div class="list-group">
        <?php foreach($etablissements as $etablissement): ?>
            <div class="list-group-item clearfix <?php if($etablissement->_id == $activeObject->_id): ?>active-bordered<?php endif; ?>">
                <?php include_partial('etablissement/bloc', array('etablissement' => $etablissement)); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="panel-footer text-center">
        <a class="btn btn-xs btn-link" href="<?php echo url_for('etablissement_ajout', array('identifiant' => $societe->identifiant)); ?>"><span class="glyphicon glyphicon-plus-sign"></span> Créer un établissement</a>
    </div>
</div>
<?php $compteSociete = $societe->getMasterCompte(); ?>
<?php $compteSociete->updateCoordonneesLongLat() ?>
<div class="carte" data-point='<?php echo json_encode(array_values($compteSociete->getRawValue()->getCoordonneesLatLon())) ?>'  style="height: 180px; border-radius: 4px; margin-bottom: 10px;"></div>
<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">Interlocuteurs</h3></div>
    <?php if(count($interlocuteurs)): ?>
    <div class="list-group">
        <?php foreach ($interlocuteurs as $interlocuteurId => $interlorcuteur) : ?>
            <div class="list-group-item clearfix <?php if($interlorcuteur->_id == $activeObject->_id): ?>active-bordered<?php endif; ?>">
                <?php include_partial('compte/bloc', array('compte' => $interlorcuteur)); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="panel-body text-center">
        <span class="text-muted">Aucun interlocuteur</span>
    </div>
    <?php endif; ?>
    <div class="panel-footer text-center">
        <a class="btn btn-xs btn-link" href="<?php echo url_for('compte_ajout', array('identifiant' => $societe->identifiant)); ?>"><span class="glyphicon glyphicon-plus-sign"></span> Créer un interlocuteur</a>
    </div>
</div>
