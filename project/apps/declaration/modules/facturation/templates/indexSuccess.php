<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
</ol>

<div class="row row-margin">
    <div class="col-xs-12">
        <form method="post" action="" role="form" class="form-horizontal">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <div class="form-group">
                <?php echo $form["login"]->renderError(); ?>
                <div class="col-sm-8 col-sm-offset-1 col-xs-12">
                    <?php echo $form["login"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocompleteremote select2SubmitOnChange",
                                    "placeholder" => "Se connecter à un opérateur",
                                    "autofocus" => "autofocus",
                                    "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                                    )); ?>
                </div>
                <div class="col-sm-2 hidden-xs">
                    <button class="btn btn-default btn-lg" type="submit">Se connecter</button>
                </div>
            </div>

        </form>
    </div>
</div>

<?php include_partial('generation/list', array('generations' => $generations)); ?>
