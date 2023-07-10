<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_LOTS)); ?>

<div class="page-header no-border">
    <h2>Sélection des opérateurs <small class="text-muted">Campagne <?php echo $degustation->campagne; ?></small></h2>
</div>

<p>Sélectionnez l'ensemble des opérateurs à prélever pour la dégustation</p>
<form action="<?php echo url_for("degustation_selection_operateurs", $degustation) ?>" method="post" class="form-horizontal degustation prelevements selectionlots">
    <?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <?php echo $form['identifiant']->render() ?>

    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th class="col-xs-3">Opérateur</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($degustation->getLots() as $key => $lot): ?>
            <tr><td>
                <?php echo $lot->declarant_nom ?> - <?php echo $lot->adresse_logement ?>
            </td></tr>
        <?php  endforeach; ?>
        </tbody>
    </table>

    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation") ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right">
            <a href="<?php echo url_for(DegustationEtapes::getInstance()->getNextLink(DegustationEtapes::ETAPE_LOTS), $degustation) ?>"
            class="btn btn-primary">
                Étape suivante <span class="glyphicon glyphicon-chevron-right"></span>
            </a>
        </div>
    </div>
</form>
</div>

