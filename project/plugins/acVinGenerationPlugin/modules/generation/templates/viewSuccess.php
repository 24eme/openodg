<?php use_helper("Generation"); ?>
<?php use_helper("Date"); ?>
<?php use_helper("Float"); ?>

<div class="page-header no-border">
    <h2>
      Génération N° <?= ($generation->getMasterGeneration()) ? $generation->getMasterGeneration()->identifiant.' '.$generation->type_document : $generation->identifiant; ?><small> créé le <?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->date_maj); ?></small>
    </h2>
</div>

<?php if($generation->libelle): ?>
<p class="text-center lead">
    <?php echo Anonymization::hideIfNeeded($generation->libelle); ?>
</p>
<?php endif; ?>

<?php if(count($generation->arguments) > 0): ?>
<p class="text-center text-muted">
    <?php foreach ($generation->arguments as $key => $argument) : ?>
        <span><?php echo ucfirst(getLabelForKeyArgument($key)) ?></span> <?php echo $argument; ?><br />
    <?php endforeach; ?>
</p>
<?php endif; ?>

<p class="text-center lead">
    <?php echo $generation->nb_documents; ?> document<?php if($generation->nb_documents > 1): ?>s<?php endif; ?>
    <?php if($generation->somme): ?><small class="text-muted">(<?php echo echoFloat($generation->somme) ?> €)</small><?php endif; ?>
</p>

<p class="text-center lead">
    <span class="label label-<?php echo statutToCssClass($generation->statut) ?>"><span class="<?php echo statutToIconCssClass($generation->statut) ?>"></span>&nbsp;&nbsp;<?php echo statutToLibelle($generation->statut); ?></span>
</p>

<p class="text-center">
<small class="text-muted">(Mis à jour le <?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->date_maj); ?>)</small>
</p>

<?php if ($generation->message) : ?>
    <div class="alert alert-<?php if($generation->statut == GenerationClient::GENERATION_STATUT_ENERREUR): ?>danger<?php else: ?>warning<?php endif; ?>" style="max-height: 200px; overflow: auto">
        <?php echo nl2br($generation->message); ?>
    </div>
<?php endif; ?>

<?php if ($generation->statut == GenerationClient::GENERATION_STATUT_GENERE):?>
<div class="row">
  <div class="col-xs-6 col-xs-offset-3">
    <?php foreach ($generation->fichiers as $chemin => $titre): ?>
      <p>
        <a download="<?php echo basename(urldecode($chemin)) ?>" href="<?php echo urldecode($chemin); ?>?<?php echo $generation->_rev ?>"  target="_blank" class="list-group-item text-center"><span class="glyphicon glyphicon-download-alt"></span>&nbsp;&nbsp;<?php echo $titre; ?></a>
      </p>
    <?php endforeach; ?>
    <?php foreach($sous_generations as $sous_generation): ?>
        <?php foreach ($sous_generation->fichiers as $chemin => $titre): ?>
            <p style="position: relative;">
                <a class="list-group-item text-center" download="<?php echo basename(urldecode($chemin)) ?>" href="<?php echo urldecode($chemin); ?>"  target="_blank"><span class="glyphicon glyphicon-download-alt"></span>&nbsp;&nbsp;<?php echo $titre; ?></a>
                <a class="btn btn-link" style="position: absolute; top: 5px; right: -40px" href="<?= url_for('generation_view',['id' => $sous_generation->_id]) ?>"><span class="glyphicon glyphicon-eye-open"></span></a>
            </p>
        <?php endforeach; ?>
    <?php endforeach; ?>


    <?php if($generation->exist('sous_generation_types')): ?>
    <?php foreach ($generation->sous_generation_types as $sous_generation_type): ?>
        <?php $sousGenerationClass = GenerationClient::getInstance()->getClassForGeneration($generation->getOrCreateSubGeneration($sous_generation_type)); ?>
        <p>
        <?php if (count($generation->getOrCreateSubGeneration($sous_generation_type)->fichiers)): continue; endif; ?>
          <?php if($generation->getOrCreateSubGeneration($sous_generation_type)->isNew()) :?>
          <a onclick="return confirm('Étes vous sûr de vouloir <?php echo lcfirst(str_replace("'", '\\\'', $sousGenerationClass::getActionLibelle())) ?> ?')" title="<?php echo str_replace('"', '', $sousGenerationClass::getActionDescription()) ?>" class="btn btn-link" href="<?= url_for('facturation_sous_generation', [
            'generation' => $generation->_id,
            'type' => $sous_generation_type
          ]) ?>"><span class="glyphicon glyphicon-play-circle"></span>&nbsp;<?php echo $sousGenerationClass::getActionLibelle() ?></a>
          <?php else: ?>
              <a class="btn btn-link" href="<?= url_for('generation_view', [
                'id' => $generation->getOrCreateSubGeneration($sous_generation_type)->_id]) ?>"><span class="glyphicon glyphicon-eye-open"></span>&nbsp;<?php echo $sousGenerationClass::getActionLibelle() ?></a>
          <?php endif; ?>
        </p>
    <?php endforeach ?>
    <?php endif ?>
  </div>
</div>
<?php endif; ?>

<div class="row row-margin">
    <div class="col-xs-4 text-left">
        <?php if(isset($backUrl) && $backUrl): ?>
        <a class="btn btn-default" href="<?php echo $backUrl ?>"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;&nbsp;Retour</a>
        <?php endif; ?>
    </div>
    <?php if(
                ($generation->statut == GenerationClient::GENERATION_STATUT_ENERREUR) ||
                ($generation->statut == GenerationClient::GENERATION_STATUT_RELANCABLE) || 
                ($generation->statut == GenerationClient::GENERATION_STATUT_GENERE && $generation->message)
             ): ?>
    <div class="col-xs-4 text-center">
        <a class="btn btn-<?php if($generation->statut == GenerationClient::GENERATION_STATUT_ENERREUR): ?>danger<?php else: ?>warning<?php endif; ?> btn-upper" href="<?php echo url_for('generation_reload', ['id' => $generation->_id]); ?>"><span class="glyphicon glyphicon-refresh"></span>&nbsp;&nbsp;Relancer</a>
    </div>
    <?php endif; ?>
</div>

<?php if ($generation->getMasterGeneration()): ?>
<a href="<?= url_for('generation_view', ['id' => $generation->getMasterGeneration()->_id]) ?>" class="btn btn-default"><i class="glyphicon glyphicon-chevron-left"></i> Retour</a>
<?php endif ?>


<?php if(in_array($generation->statut, array(GenerationClient::GENERATION_STATUT_ENATTENTE, GenerationClient::GENERATION_STATUT_ENCOURS))): ?>
<script type="text/javascript">window.setTimeout("window.location.reload()", 30000);</script>
<?php endif; ?>
