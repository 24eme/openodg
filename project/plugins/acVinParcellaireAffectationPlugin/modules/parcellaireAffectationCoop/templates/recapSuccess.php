<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'apporteurs', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border">
    <h2>Visualisation des changements</h2>
</div>

<table class="table table-condensed table-bordered">
    <tr>
        <th class="text-right col-xs-1">Provenance</th>
        <th class="col-xs-1">CVI</th>
        <th class="col-xs-3">Nom</th>
        <th>Changemement</th>
        <th class="text-right col-xs-1"></th>
    </tr>
<?php
foreach ($parcellaireAffectationCoop->getApporteursChanges() as $apporteur): ?>
    <tr class="vertical-center cursor-pointer">
        <td class="text-center">
            <?php echo $apporteur->provenance; ?>
        </td>
        <td >
            <?php echo $apporteur->cvi; ?>
        </td>
        <td >
            <?php echo $apporteur->nom; ?>
        </td>
        <td class="<?php if($apporteur->apporteur): ?>bg-success<?php else: ?>bg-danger<?php endif; ?>">
        <?php if($apporteur->apporteur): ?>
            <span class="glyphicon glyphicon-check"></span> L'apporteur a été coché 
        <?php elseif(!$apporteur->apporteur): ?>
            <span class="glyphicon glyphicon-unchecked"></span> L'apporteur a été décoché 
        <?php endif; ?>
        </td>
        <td>
        <a href="<?php echo url_for("etablissement_visualisation", array('identifiant' => $apporteur->getEtablissementIdentifiant())) ?>" class="btn btn-default btn-xs">Voir l'établissement&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
        </td>
    </tr>
<?php endforeach; ?>
</table>


<div class="row row-margin row-button">
    <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectationcoop_apporteurs", $parcellaireAffectationCoop) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
</div>
