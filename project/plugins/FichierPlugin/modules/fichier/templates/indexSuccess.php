<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('documents'); ?>">Documents</a></li>
</ol>

<div class="row row-margin">
    <div class="col-xs-12">
        <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('documents_etablissement_selection'))); ?>
    </div>
</div>
