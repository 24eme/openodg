<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('parcellaire'); ?>">Parcellaire</a></li>
</ol>

<?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('parcellaire_etablissement_selection'))); ?>
