<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_LOTS)); ?>

<div class="page-header no-border">
    <h2>Sélection des opérateurs & lots <small class="text-muted">Campagne <?php echo $degustation->campagne; ?></small></h2>
</div>

<h4>Sélectionnez l'ensemble des opérateurs à prélever pour la dégustation</h4>
<form action="<?php echo url_for("degustation_selection_operateurs", $degustation) ?>" method="post" class="form-horizontal degustation prelevements selectionlots">
    <?php echo $formOperateurs->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $formOperateurs->renderGlobalErrors(); ?>
    </div>

    <?php echo $formOperateurs['identifiant']->render() ?>

    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th class="col-xs-3">Opérateur</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($degustation->getLots() as $key => $lot): ?>
            <?php if ($lot->id_document_provenance !== null) { continue; } ?>
            <tr><td>
                <?php echo $lot->declarant_nom ?> - <?php echo $lot->adresse_logement ?>
            </td></tr>
        <?php  endforeach; ?>
        </tbody>
    </table>
</form>

<hr/>

<h4>Sélectionnez l'ensemble des lots à prélever pour la dégustation</h4>
<form action="<?php echo url_for("degustation_selection_operateurs", $degustation) ?>" method="post" class="form-horizontal degustation prelevements selectionlots">
    <?php echo $formLots->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $formLots->renderGlobalErrors(); ?>
    </div>

    <?php include_partial('degustation/tableSelectionLots', ['form' => $formLots]); ?>

    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation") ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-aleatoire-aleatoire-renforce">Ajouter un opérateur</button>
        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-primary">
                Étape suivante <span class="glyphicon glyphicon-chevron-right"></span>
            </button>
        </div>
    </div>
</form>

<?php include_partial('degustation/popupAjoutOperateur', ['degustation' => $degustation, 'form' => $formOperateurs]); ?>
