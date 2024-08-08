<div class="page-header no-border">
    <h2>Ajouter un adhérent</h2>
</div>


<form id="ajoutApporteurForm" action="" method="post" class="form-horizontal">
    <div class="row row-margin">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <?php include_partial('global/flash'); ?>

        <div class="col-xs-4"><?php echo $form['cviApporteur']->renderLabel(); ?></div>
        <div class="col-xs-4">
            <?php echo $form['cviApporteur']->render(); ?>
        </div>
    </div>



    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectationcoop_apporteurs", $parcellaireAffectationCoop); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Ajouter</button></div>
    </div>
</form>
