<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>

<section class='container'>

<h2>Sélectionnez l'ensemble des lots à prélever pour la dégustation</h2>

<form action="<?php echo url_for("degustation_selection_operateurs_add", $degustation) ?>" method="post">
    <?php echo $formOperateurs->renderHiddenFields(); ?>

    <div class="bg-danger">
        <?php echo $formOperateurs->renderGlobalErrors(); ?>
    </div>

    <div class="form-group">
        <?php echo $formOperateurs['initial_type']->renderLabel(null, ['class' => 'col-sm-3 control-label']) ?>
        <div class="col-sm-9">
            <?php echo $formOperateurs['initial_type']->render() ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo $formOperateurs['identifiant']->renderLabel(null, ['class' => 'col-sm-3 control-label']) ?>
        <div class="col-sm-9">
            <?php echo $formOperateurs['identifiant']->render() ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo $formOperateurs['details']->renderLabel(null, ['class' => 'col-sm-3 control-label']) ?>
        <div class="col-sm-9">
            <?php echo $formOperateurs['details']->render() ?>
        </div>
    </div>


<div class="col-xs-6">
    <a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
</div>
<div class="col-xs-5 text-right">
    <input class="btn btn-primary" type="submit" value="Ajouter l'opérateur" />
</div>
</form>
</section>
