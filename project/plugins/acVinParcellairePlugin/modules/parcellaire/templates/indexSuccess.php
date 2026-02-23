<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('parcellaire'); ?>">Parcellaire</a></li>
</ol>

<?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('parcellaire_etablissement_selection'))); ?>

<?php if(class_exists("ControleConfiguration") && ControleConfiguration::getInstance()->isModuleEnabled()): ?>
<div class="row col-xs-10">
    <a href="<?php echo url_for('controle_index') ?>" class="btn btn-primary">Accéder au contrôle terrain</a>
</div>
<div class="col-xs-2">
    <a href="<?php echo url_for('controle_gestion_manquements', array())?>" class="btn btn-primary">Gérer les manquements</a>
</div>
<?php endif; ?>
