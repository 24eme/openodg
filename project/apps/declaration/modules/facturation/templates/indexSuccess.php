<div class="row row-margin">
    <form method="post" action="" role="form" class="form-horizontal col-lg-6">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="form-group">
    		<?php echo $form["declarant"]->renderLabel() ?>
    		<p class="text-danger"><?php echo $form["declarant"]->renderError() ?></p>
    		<?php echo $form["declarant"]->render(array("class" => "form-control input-lg")); ?>
  		</div>
  		<div class="form-group">
    		<?php echo $form["type_facture"]->renderLabel() ?>
    		<p class="text-danger"><?php echo $form["type_facture"]->renderError() ?></p>
    		<?php echo $form["type_facture"]->render(array("class" => "form-control input-lg")); ?>
  		</div>
        <button class="btn btn-default btn-lg" type="submit">Editer facture</button>

    </form>  
    <div class="col-lg-12">
    <?php if (count($values) > 0): ?><p class="text-success"><?php echo implode(', ', $values); ?></p><?php endif; ?>
    </div>
</div>
