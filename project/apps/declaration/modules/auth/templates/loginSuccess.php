<div class="row row-margin">
    <form method="post" action="" role="form">

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
    <div class="col-xs-offset-2 col-xs-8">
        <a href="<?php echo  url_for('compte_creation_admin'); ?>" class="btn btn-warning">Créer un compte</a>
        <a href="#" class="btn btn-danger">Créer un établissement</a>
        <a href="<?php echo  url_for('compte_recherche'); ?>" class="btn btn-info">Rechercher un compte</a>
    </div>
</div>