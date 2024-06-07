<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>

<section class="row">

    <div class="col-xs-10 col-xs-offset-1">

        <h2 style="margin-bottom: 30px;">Sélectionnez l'ensemble des lots à prélever pour la dégustation</h2>

        <form action="<?php echo url_for("degustation_selection_operateurs_add", $degustation) ?>" method="post" class="form-horizontal">
            <?php echo $formOperateurs->renderHiddenFields(); ?>

            <div class="bg-danger">
                <?php echo $formOperateurs->renderGlobalErrors(); ?>
            </div>

            <div class="form-group">
                <?php echo $formOperateurs['initial_type']->renderLabel(null, ['class' => 'col-sm-3 control-label']) ?>
                <div class="col-sm-4">
                    <?php echo $formOperateurs['initial_type']->render() ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $formOperateurs['identifiant']->renderLabel(null, ['class' => 'col-sm-3 control-label']) ?>
                <div class="col-sm-8">
                    <?php echo $formOperateurs['identifiant']->render() ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $formOperateurs['details']->renderLabel(null, ['class' => 'col-sm-3 control-label']) ?>
                <div class="col-sm-4">
                    <?php echo $formOperateurs['details']->render() ?>
                    <?php echo $formOperateurs['liste-appellations']->render(); ?>
                </div>
            </div>

            <?php if(!$etablissement_identifiant): ?>
            <div class="row" style="margin-top: 40px;">
                <div class="col-xs-6">
                    <a class="btn btn-default btn pull-left" href="">Annuler</a>
                </div>
                <div class="col-xs-6 text-right">
                    <button class="btn btn-primary" type="submit">Valider</a>
                </div>
            </div>
            <?php endif; ?>

            <hr />
            <div <?php if(!$etablissement_identifiant): ?>style="opacity: 0.5;"<?php endif; ?> class="form-group">
                <?php echo $formOperateurs['chai']->renderLabel(null, ['class' => 'col-sm-3 control-label']) ?>
                <div class="col-sm-6">
                    <?php echo $formOperateurs['chai']->render() ?>
                </div>
            </div>

            <?php if($etablissement_identifiant): ?>
            <div class="row" style="margin-top: 40px;">
                <div class="col-xs-6">
                    <a class="btn btn-default btn pull-left" href="">Annuler</a>
                </div>
                <div class="col-xs-6 text-right">
                    <button class="btn btn-primary" type="submit">Ajouter l'opérateur</a>
                </div>
            </div>
            <?php endif; ?>

        </form>
    </div>
</section>
