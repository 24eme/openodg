<div class="row row-margin">
    <form method="post" action="<?php echo url_for("auth_login") ?>" role="form" class="form-horizontal">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="form-group">
            <?php echo $form["login"]->renderError(); ?>
            <div class="col-xs-8 col-xs-offset-1">
                <?php echo $form["login"]->render(array("class" => "form-control input-lg",
                                                        "autofocus" => "autofocus",
                                                        "placeholder" => "Se connecter à un opérateur")); ?>
            </div>
            <div class="col-xs-2">
                <button class="btn btn-default btn-lg" type="submit">Se connecter</button>
            </div>
        </div>

    </form>
</div>
