<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('tournee' => $tournee )); ?>
<?php include_partial('degustation/stepSaisie', array('tournee' => $tournee, 'active' => TourneeSaisieEtapes::ETAPE_SAISIE_VALIDATION)); ?>

<div class="page-header">
    <h2>Validation de la dégustation du <?php echo format_date($tournee->date, "P", "fr_FR") ?></span></h2></h2>
</div>

<?php if ($validation->hasPoints()): ?>
    <?php include_partial('degustation/pointsAttentions', array('tournee' => $tournee, 'validation' => $validation)); ?>
<?php endif; ?>

<form action="<?php echo url_for('degustation_saisie_validation', $tournee); ?>" method="post" class="form-horizontal">

    <?php include_partial('degustation/recap', array('tournee' => $tournee)); ?>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation_saisie_degustateurs', $tournee) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" onclick="return confirm('Étes-vous sur de valider')" class="btn btn-default btn-lg btn-upper">Valider&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
        </div>
    </div>
</form>
