<div class="row row-margin">
    <form method="post" action="" role="form">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="col-xs-offset-3">
        <?php echo $form["login"]->renderError(); ?>
        <?php echo $form["login"]->renderLabel(null, array("class" => "control-label")); ?>
        <?php echo $form["login"]->render(array("class" => "")); ?>

        <button type="submit" class="btn btn-default">Se connecter</button>
    </div>
    </form>
</div>