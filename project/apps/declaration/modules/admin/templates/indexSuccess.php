<ul class="nav nav-tabs">
  <li role="presentation" class="active"><a href="<?php echo url_for('admin'); ?>">Déclarations</a></li>
  <li role="presentation"><a href="<?php echo url_for('compte_recherche'); ?>">Contacts</a></li>
</ul>

<div class="row row-margin">
    <form method="post" action="" role="form" class="form-horizontal">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="form-group">
            <?php echo $form["login"]->renderError(); ?>
            <div class="col-xs-8 col-xs-offset-1">
                <?php echo $form["login"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocompleteremote",
                                "placeholder" => "Se connecter à un opérateur",
                                "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                                )); ?>
            </div>
            <div class="col-xs-2">
                <button class="btn btn-default btn-lg" type="submit">Se connecter</button>
            </div>
        </div>

    </form>
</div>

<?php include_component('admin', 'list'); ?>