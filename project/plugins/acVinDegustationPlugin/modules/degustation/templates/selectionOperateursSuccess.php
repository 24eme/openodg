<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_LOTS)); ?>

<div class="page-header no-border">
    <h2>Sélection des opérateurs & lots <small class="text-muted">Campagne <?php echo $degustation->campagne; ?></small></h2>
</div>

<h4>Sélectionnez l'ensemble des lots à prélever pour la dégustation</h4>
<form action="<?php echo url_for("degustation_selection_operateurs", $degustation) ?>" method="post" class="form-horizontal degustation prelevements selectionlots">
    <?php echo $formLots->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $formLots->renderGlobalErrors(); ?>
    </div>

    <?php include_partial('degustation/tableSelectionLots', ['degustation' => $degustation, 'form' => $formLots]); ?>
    <div class="col-xs-12 text-center">
        <a href="<?php echo url_for('degustation_selection_operateurs_add', $degustation) ;?>" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Ajouter un opérateur</a>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation") ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">

        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-primary">
                Étape suivante <span class="glyphicon glyphicon-chevron-right"></span>
            </button>
        </div>
    </div>
</form>
