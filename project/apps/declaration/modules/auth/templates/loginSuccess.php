<div class="row row-margin">
    <form method="post" action="" role="form">

        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="col-xs-offset-4 col-xs-4">
            <div class="form-group">
            <?php echo $form["login"]->renderError(); ?>
            <?php echo $form["login"]->renderLabel(null, array("class" => "control-label")); ?>
            <?php echo $form["login"]->render(array("class" => "")); ?>
            </div>

            <button type="submit" class="btn btn-default">Se connecter</button>
        </div>
    </form>
</div>