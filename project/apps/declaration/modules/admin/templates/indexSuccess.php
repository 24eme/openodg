<div class="page-header">
    <h2>Administration</h2>
</div>

<div class="row row-margin">
    <form method="post" action="" role="form" class="form-horizontal">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="form-group">
            <?php echo $form["login"]->renderError(); ?>
            <div class="col-xs-8 col-xs-offset-1">
                <?php echo $form["login"]->render(array("class" => "form-control select2 select2-offscreen select2autocompleteremote",
                                "placeholder" => "Se connecter à un opérateur",
                                "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                                )); ?>
            </div>
            <div class="col-xs-2">
                <button class="btn btn-default btn-default-step" type="submit">Se connecter</button>
            </div>
        </div>

    </form>
</div>

<h3>Comptes</h3>
<div class="row row-margin">
    <form method="post" action="<?php echo url_for('compte_choice_creation_admin'); ?>" name="<?php echo $formChoiceCreateCompte->getName(); ?>" role="form" class="form-horizontal">

        <?php echo $formChoiceCreateCompte->renderHiddenFields(); ?>
        <?php echo $formChoiceCreateCompte->renderGlobalErrors(); ?>
        <div class="form-group">
            <?php echo $formChoiceCreateCompte["type_compte"]->renderError(); ?>
            <?php //echo $formChoiceCreateCompte["type_compte"]->renderLabel(null, array("class" => "control-label col-xs-2")); ?>
            <div class="col-xs-6 col-xs-offset-2">

                <?php echo $formChoiceCreateCompte["type_compte"]->render(array("data-placeholder" => "Type de Compte", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
            </div>
            <button type="submit" class="btn btn-default">Créer Compte</button>
            <a href="<?php echo url_for('compte_recherche'); ?>" class="btn btn-info">Rechercher un compte</a>

        </div>
    </form>     
</div>


<h3>Documents</h3>

<?php include_component('admin', 'list'); ?>