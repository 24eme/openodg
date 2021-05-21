<?php use_helper('Date'); ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Affectation parcellaire de vos apporteurs</h3>
        </div>
        <div class="panel-body">
            <p class="explications">Vous pouvez déclarer les affectations parcellaires de vos apporteurs</p>
            <div class="actions">
                <a id="btn_affection_parcellaire_coop" class="btn btn-block btn-default" href="<?php echo url_for('parcellaireaffectationcoop_sv11', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Déclarer pour vos apporteurs</a>
            </div>
        </div>
    </div>
</div>

