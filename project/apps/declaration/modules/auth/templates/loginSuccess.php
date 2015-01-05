<div class="row row-margin">
    <form method="post" action="" role="form" name="<?php echo $form->getName(); ?>">

        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="col-xs-offset-2 col-xs-8">
            <div class="form-group">
                <?php echo $form["login"]->renderError(); ?>
                <?php echo $form["login"]->renderLabel(null, array("class" => "control-label")); ?>
                <?php echo $form["login"]->render(array("data-placeholder" => "Séléctionnez un établissement", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
            </div>

            <button type="submit" class="btn btn-default">Se connecter</button>
        </div>

    </form>    
</div>
<div class="row row-margin">

    <form method="post" action="<?php echo url_for('compte_choice_creation_admin'); ?>" name="<?php echo $formChoiceCreateCompte->getName(); ?>" role="form">

        <?php echo $formChoiceCreateCompte->renderHiddenFields(); ?>
        <?php echo $formChoiceCreateCompte->renderGlobalErrors(); ?>
        <div class="col-xs-offset-2 col-xs-8">
            <div class="form-group">
                <?php echo $formChoiceCreateCompte["type_compte"]->renderError(); ?>
                <?php echo $formChoiceCreateCompte["type_compte"]->renderLabel(null, array("class" => "control-label")); ?>
                <?php echo $formChoiceCreateCompte["type_compte"]->render(array("data-placeholder" => "Type de Compte", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
            </div>

            <button type="submit" class="btn btn-default">Créer Compte</button>
            <a href="<?php echo  url_for('compte_recherche'); ?>" class="btn btn-info">Rechercher un compte</a>
        </div>
    </form>    
</div>