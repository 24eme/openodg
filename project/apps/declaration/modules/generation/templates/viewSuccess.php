<?php use_helper("Generation"); ?>
<?php use_helper("Date"); ?>
<?php use_helper("Float"); ?>

<?php include_partial('admin/menu', array('active' => 'facturation')); ?>

<div class="page-header no-border">
    <div class="btn-group pull-right">
        <?php if($generation->statut == GenerationClient::GENERATION_STATUT_GENERE): ?>
        <a href="<?php echo url_for('generation_delete', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission)); ?>" class="btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer</a>
        <?php endif; ?>
    </div>
    <h2>Génération N° <?php echo $generation->identifiant; ?><small> créé le <?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->date_maj); ?></small></h2>
</div>

<?php if(count($generation->arguments) > 0): ?>
<p class="text-center text-muted">
    <?php foreach ($generation->arguments as $key => $argument) : ?>
        <?php echo ucfirst(getLabelForKeyArgument($key)) ?></strong> <?php echo $argument; ?><br />
    <?php endforeach; ?>
</p>
<?php endif; ?>

<p class="text-center lead">
    <?php echo $generation->nb_documents; ?> <?php echo strtolower($type); ?><?php if($generation->nb_documents > 1): ?>s<?php endif; ?>
    <small class="text-muted">(<?php echo echoFloat($generation->somme) ?> €)</small>
</p>

<p class="text-center lead">
    <span class="label label-<?php echo statutToCssClass($generation->statut) ?>"><span class="<?php echo statutToIconCssClass($generation->statut) ?>"></span>&nbsp;&nbsp;<?php echo statutToLibelle($generation->statut); ?></span>
</p>

<p class="text-center">
<small class="text-muted">(Mis à jour le <?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->date_maj); ?>)</small>
</p>

<?php if ($generation->message) : ?>
    <div class="alert alert-danger" style="max-height: 200px; overflow: auto">
        <?php echo $generation->message; ?>
    </div>
<?php endif; ?>

<?php if ($generation->statut == GenerationClient::GENERATION_STATUT_GENERE && count($generation->fichiers)) : ?>
<div class="row row-margin">
<div class="list-group col-xs-6 col-xs-offset-3">
    <?php foreach ($generation->fichiers as $chemin => $titre): ?>
        <a href="<?php echo urldecode($chemin); ?>"  target="_blank" class="list-group-item text-center"><span class="glyphicon glyphicon-download-alt"></span>&nbsp;&nbsp;<?php echo $titre; ?></a>
    <?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<div class="row row-margin">
    <div class="col-xs-4 text-left">
        <a class="btn btn-default btn-default-step btn-lg btn-upper" href="<?php echo url_for('facturation') ?>"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-4 text-center">
        <?php if($generation->statut == GenerationClient::GENERATION_STATUT_ENERREUR): ?>
            <a class="btn btn-warning btn-lg btn-upper" href="<?php echo url_for('generation_reload', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission)); ?>"><span class="glyphicon glyphicon-repeat"></span>&nbsp;&nbsp;Relancer</a>
        <?php endif; ?>
    </div>
</div>

<?php if(in_array($generation->statut, array(GenerationClient::GENERATION_STATUT_ENATTENTE, GenerationClient::GENERATION_STATUT_ENCOURS))): ?>
<script type="text/javascript">window.setTimeout("window.location.reload()", 30000);</script>
<?php endif; ?>
 