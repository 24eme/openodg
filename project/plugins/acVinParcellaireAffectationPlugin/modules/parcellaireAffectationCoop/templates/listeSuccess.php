<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border">
    <h2>Saisie des affectations parcellaires par apporteur</h2>
</div>

<div class="row">
    <div class="form-group col-xs-10">
      <input id="hamzastyle" type="hidden" data-placeholder="Sélectionner un filtre" data-hamzastyle-container=".table_affectations" data-hamzastyle-mininput="3" class="select2autocomplete hamzastyle form-control">
    </div>
</div>

<form action="" method="post" class="form-horizontal">
    <table class="table table-condensed table-striped table-bordered table_affectations">
        <tr>
            <th class="col-xs-1">CVI</th>
            <th>Nom</th>
            <th class="col-xs-1 text-center">Statut</th>
            <th class="col-xs-2"></th>
        </tr>
    <?php foreach ($apporteurs as $idApporteur => $apporteur): ?>
        <tr class="hamzastyle-item" data-words='<?php echo json_encode(array($apporteur->cvi, $apporteur->nom), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>' >
            <td><?php echo $apporteur->cvi; ?></td>
            <td><?php echo $apporteur->nom; ?></td>
            <td style="<?php if(isset($documents[$idApporteur])): ?>background-color: rgba(169, 197, 50, 0.4) ;<?php endif; ?>" class="text-center <?php if(isset($documents[$idApporteur])): ?>bg-success text-success<?php endif; ?>"><?php if(isset($documents[$idApporteur])): ?><span class="glyphicon glyphicon-ok-sign"></span> Validé<?php else: ?>À saisir<?php endif; ?></a></td>
            <td class="text-center">
                <?php if(isset($documents[$idApporteur])): ?>
                    <a class="text-success" href="<?php echo url_for('parcellaireaffectationcoop_visualisation', array('sf_subject' => $parcellaireAffectationCoop, 'id_document' => $documents[$idApporteur])) ?>">Voir la déclaration</a>
                <?php else: ?>
                    <a class="btn_saisie_affectation_parcellaire" href="<?php echo url_for('parcellaireaffectationcoop_saisie', array('sf_subject' => $parcellaireAffectationCoop, 'apporteur' => str_replace("ETABLISSEMENT-", "",$idApporteur))) ?>">Saisir la déclaration</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </table>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectationcoop_apporteurs", $parcellaireAffectationCoop) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("parcellaireaffectationcoop_exportcsv", $parcellaireAffectationCoop) ?>" class="btn btn-primary">Export CSV</a>
        </div>
        <div class="col-xs-4 text-right"></div>
    </div>
</form>

<?php use_javascript('hamza_style.js'); ?>
