<?php use_helper("Date"); ?>

<div class="row row-margin text-center">
    <h2>Tournées</h2>
    <h2><?php echo ucfirst(format_date(date('Y-m-d'), "P", "fr_FR")); ?></h2>
</div>
<form id="form_ajout_agent_tournee" action="<?php echo url_for('tournee_agent_accueil'); ?>" method="post" class="form-horizontal" name="<?php echo $form->getName(); ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>


    <div class="row row-margin">
        <div class="col-xs-8 col-xs-offset-2">
            <div class="form-group <?php if ($form["agent"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["agent"]->renderError(); ?>
                <?php echo $form["agent"]->renderLabel(); ?>
                <?php echo $form["agent"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "placeholder" => "Votre nom")); ?>
            </div>
        </div>

    </div>
    <div class="row row-margin">
        <div class="col-xs-8 col-xs-offset-2">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Valider</button>
        </div>
    </div>
</form>