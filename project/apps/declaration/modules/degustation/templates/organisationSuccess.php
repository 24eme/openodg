<div class="page-header">
    <h2>Organisation des tournées</h2>
</div>

<form id="form_degustation_operateurs" action="" method="post" class="form-horizontal ajaxForm">

    <?php include_partial("degustation/organisation", array('tournee' => $tournee, 'couleurs' => $couleurs, 'heures' => $heures, 'operateurs' => $operateurs, 'agents_couleur' => $agents_couleur)); ?>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation_visualisation', $tournee) ?>" class="btn btn-primary btn-lg btn-upper">Retour</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Valider</button>
        </div>
    </div>
</form>