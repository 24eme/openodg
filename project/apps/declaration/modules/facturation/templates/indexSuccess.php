<?php include_partial('admin/menu', array('active' => 'facturation')); ?>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-6 col-xs-offset-3">
            <div class="form-group <?php if($form["declarant"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["declarant"]->renderError() ?>
        		<?php echo $form["declarant"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocompleteremote",
                                "placeholder" => "Séléctionnez un opérateur",
                                "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                                )); ?>
      		</div>
      		<div class="form-group <?php if($form["template_facture"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["template_facture"]->renderError() ?>
        		<?php echo $form["template_facture"]->render(array("class" => "form-control input-lg")); ?>
      		</div>
            <div class="form-group text-right">
                <div class="col-xs-6 col-xs-offset-6">
                    <button class="btn btn-default btn-lg btn-block btn-upper" type="submit">Générer la facture</button>
                </div>
            </div>
            
        </div>
    </div>
</form>  

<?php include_partial('generation/list', array('generations' => $generations)); ?>