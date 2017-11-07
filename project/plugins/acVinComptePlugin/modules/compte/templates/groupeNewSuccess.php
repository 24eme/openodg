<?php use_helper('Compte'); ?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('societe') ?>">Contacts</a></li>
    <li>Nouveau groupes</li>
</ol>
<div class="row">
  <div class="col-xs-12">
    <form method="post" class="form-horizontal" action="<?php echo url_for('compte_new_groupe'); ?>">
      <?php echo $form->renderHiddenFields() ?>
      <?php echo $form->renderGlobalErrors() ?>
      <div class="col-xs-7">
        <div class="form-group <?php if($form['nom_groupe']->hasError()): ?> has-error<?php endif; ?>">
            <?php echo $form['nom_groupe']->renderError(); ?>
            <?php echo $form['nom_groupe']->render(); ?>
        </div>
      </div>
      <div class="col-xs-2">
      <button class="btn btn-default btn-md" type="submit" id="btn_rechercher">Nouveau groupe</button>
      </div>
    </form>
  </div>
</div>
