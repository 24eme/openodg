<h2>Calcul du potentiel de production</h2>

<form style="margin-top: 20px;" role="form" action="" method="post" id="calcul_pp" class="form-horizontal">
  <?php echo $form->renderHiddenFields(); ?>
  <?php echo $form->renderGlobalErrors(); ?>

  <div class="row form-group">
    <?php echo $form['dgc']->renderLabel("DGC :", array('class' => "col-sm-3 control-label")); ?>
    <div class="col-xs-9" style="padding:10px;">
      <?php echo $form['dgc']->render(); ?>
      <?php echo $form['dgc']->renderError(); ?>
    </div>
  </div>

  <div class="row form-group">
  <?php foreach ($form->getCepages() as $cepage): ?>
    <?php $name = $form->getCepageKey($cepage); ?>
    <?php echo $form[$name]->renderLabel($cepage . " :", array('class' => "col-sm-3 control-label")); ?>
    <div class="col-xs-9" style="padding:10px;">
      <?php echo $form[$name]->render(); ?>
      <?php echo $form[$name]->renderError(); ?>
    </div>
  <?php endforeach; ?>
  </div>

  <div class="form-group row row-margin row-button">
    <div class="col-xs-12 text-right">
        <button type="submit" class="btn btn-primary btn-upper">Calculer</button>
    </div>
  </div>

</form>
