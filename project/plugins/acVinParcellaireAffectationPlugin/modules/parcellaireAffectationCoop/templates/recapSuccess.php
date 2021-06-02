<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>

<div class="page-header no-border">
    <h2>Liste des liaisons pour les apporteurs à la cave coop <?php echo $parcellaireAffectationCoop->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireAffectationCoop->getEtablissementObject()->identifiant ?>)</h2>
</div>

<table class="table table-condensed table-striped table-bordered">
    <tr>
        <th class="text-right col-xs-1">Provenance</th>
        <th class="col-xs-1">CVI</th>
        <th>Nom</th>
        <th>Liaison</th>
        <th class="text-right col-xs-1"></th>
    </tr>
<?php
foreach ($apporteursWithDiff as $idApporteur => $apporteurWithDiff): ?>
    <?php if(!$apporteurWithDiff->remove && !$apporteurWithDiff->add):
            continue;
          endif; ?>
    <tr class="vertical-center cursor-pointer">
        <td class="text-center">
            <?php echo $apporteurWithDiff->apporteur->provenance; ?>
        </td>
        <td >
            <?php echo $apporteurWithDiff->apporteur->cvi; ?>
        </td>
        <td >
            <?php echo $apporteurWithDiff->apporteur->nom; ?>
        </td>
        <td>
        <?php if($apporteurWithDiff->remove): ?>
            <div class="col-xs-12 text-danger">
              N'apporte pas et possède une liaisons
            </div>
        <?php endif; ?>
        <?php if($apporteurWithDiff->add): ?>
            <div class="col-xs-12 text-success">
              Apporteur non référencé dans les liaisons
            </div>
        <?php endif; ?>
        </td>
        <td>
        <a href="<?php echo url_for("etablissement_visualisation", array('identifiant' => $idApporteur)) ?>" class="btn btn-default btn-xs">Traiter&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
        </td>
    </tr>
<?php endforeach; ?>
</table>


<div class="row row-margin row-button">
    <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectationcoop_liste", $parcellaireAffectationCoop) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
</div>
