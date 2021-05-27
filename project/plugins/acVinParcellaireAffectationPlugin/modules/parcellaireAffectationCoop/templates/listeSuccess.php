<div class="page-header no-border">
    <h2>Saisie des affectations parcellaires par apporteur</h2>
</div>

<form action="" method="post" class="form-horizontal">
    <table class="table table-condensed table-striped table-bordered">
        <tr>
            <th class="col-xs-1">CVI</th>
            <th>Nom</th>
            <th class="col-xs-1 text-center">Statut</th>
            <th class="col-xs-2"></th>
        </tr>
    <?php foreach ($apporteurs as $liaison): ?>
        <tr>
            <td><?php echo $liaison->cvi; ?></td>
            <td><?php echo $liaison->libelle_etablissement; ?></td>
            <td style="<?php if(isset($documents[$liaison->id_etablissement])): ?>background-color: rgba(169, 197, 50, 0.4) ;<?php endif; ?>" class="text-center <?php if(isset($documents[$liaison->id_etablissement])): ?>bg-success text-success<?php endif; ?>"><?php if(isset($documents[$liaison->id_etablissement])): ?><span class="glyphicon glyphicon-ok-sign"></span> Validé<?php else: ?>À saisir<?php endif; ?></a></td>
            <td class="text-center">
                <?php if(isset($documents[$liaison->id_etablissement])): ?>
                    <a class="text-success" href="<?php echo url_for('parcellaireaffectationcoop_visualisation', array('sf_subject' => $etablissement, 'periode' => $periode, 'id_document' => $documents[$liaison->id_etablissement])) ?>">Voir la déclaration</a>
                <?php else: ?>
                    <a class="btn_saisie_affectation_parcellaire" href="<?php echo url_for('parcellaireaffectationcoop_saisie', array('sf_subject' => $etablissement, 'apporteur' => $liaison->getEtablissementIdentifiant(), 'periode' => $periode)) ?>">Saisir la déclaration</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </table>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectationcoop_apporteurs", array('sf_subject' => $etablissement, 'periode' => $periode)) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("parcellaireaffectationcoop_exportcsv", array('sf_subject' => $etablissement, 'periode' => $periode)) ?>" class="btn btn-primary">Export CSV</a>
        </div>
        <div class="col-xs-4 text-right"></div>
    </div>
</form>
