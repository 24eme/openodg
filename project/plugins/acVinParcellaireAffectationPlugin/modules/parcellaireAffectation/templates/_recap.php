<?php use_helper('Float') ?>

<?php if ($parcellaireAffectation->hasProblemProduitCVI()): ?>
    <p class="alert alert-warning">
    Les parcelles mises en valeur pourrait rencontrer des problèmes de dénomination déclarée au CVI.
    </p>
<?php endif; ?>
<?php if ($parcellaireAffectation->hasProblemEcartPieds()): ?>
    <p class="alert alert-warning">Les parcelles dont la superficie en mise en valeur pourrait rencontrer des problèmes de densité d'après l'analyse du CVI.</p>
<?php endif; ?>
<?php if ($parcellaireAffectation->hasProblemCepageAutorise()): ?>
    <p class="alert alert-warning">Les parcelles dont le cépage est mis en valeur pourrait rencontrer des problèmes de conformité avec le cahier des charges.</p>
<?php endif; ?>
<?php if ($parcellaireAffectation->hasProblemParcellaire()): ?>
    <p class="alert alert-warning">
    Les parcelles dont l'identifiant est mis en valeur pourrait rencontrer de conformité avec votre parcellaire CVI.
    </p>
<?php endif; ?>


<?php foreach ($parcellaireAffectation->declaration->getParcellesByDgc() as $dgc => $parcelles): ?>
<?php if ($dgc): ?>
    <div class="row">
        <div class="col-xs-12">
            <h3>Dénomination complémentaire <?php echo str_replace("-", " ", $dgc); ?></h3>
        </div>
    </div>
<?php endif; ?>
<table id="parcelles_<?php echo $dgc; ?>" class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
    <thead>
        <tr>
        	<th class="col-xs-2">Commune</th>
            <th class="col-xs-2">Lieu-dit</th>
            <th class="col-xs-1">Section /<br />N° parcelle</th>
            <th class="col-xs-2">Cépage</th>
            <th class="col-xs-1">Année plantat°</th>
            <th class="col-xs-1" style="text-align: right;">Superficie affectée&nbsp;<span class="text-muted small">(ha)</span></th>
            <th class="col-xs-1">Affectation</th>
            <th class="col-xs-2">Destination</th>
        </tr>
    </thead>
    <tbody>
    <?php
        $parcelles = $parcelles->getRawValue();
        ksort($parcelles);
        $nbParcelles = 0;
        $totalSurface = 0;

        $nomCommune = null;
        $parcellesCommune = 0;
        $superficieCommune = 0;
        foreach ($parcelles as $parcelle):
    ?><?php if($parcelle->affectee): $nbParcelles++; $totalSurface += round($parcelle->superficie,4); ?>
        <?php if ($nomCommune != $parcelle->commune && $nbParcelles != 1): ?>
            <tr class="total-commune">
                <td colspan="5" class="text-right">
                    <strong>Total des <?php echo $parcellesCommune ?> parcelles de <?php echo $nomCommune ?></strong>
                </td>
                <td class="text-right"><strong><?php echoFloatFr($superficieCommune, 4) ?></strong></td>
            </tr>
            <?php $parcellesCommune = 0; $nomCommune = $parcelle->commune; $superficieCommune = 0 ?>
        <?php endif ?>
        <tr class="vertical-center<?php if ($parcelle->hasProblemProduitCVI()) echo ' warning' ?>">
            <td><?php echo $parcelle->commune; ?></td>
            <td><?php echo $parcelle->lieu; ?></td>
            <td class="text-center<?php if (!$parcelle->existsInParcellaire()) echo ' warning text-danger'; ?>">
                <?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?>
            </td>
            <td<?php if ($parcelle->hasProblemCepageAutorise()) echo ' class="warning text-danger"'; ?>>
                <?php echo $parcelle->cepage; ?>
            </td>
            <td class="text-center<?php if ($parcelle->hasProblemEcartPieds()) echo ' warning text-danger'; ?>"><?php echo $parcelle->campagne_plantation; ?></td>
            <td class="text-right"><span><?php echoFloatFr($parcelle->superficie, 4); ?></span></td>
            <?php if($parcellaireAffectation->isValidee()): ?>
            <?php endif; ?>
            <td class="text-center">
                <?php if ($parcelle->isPartielle()): ?>Partielle<?php else: ?>Totale<?php endif; ?>
            </td>
            <td><?php echo implode(", ", $parcelle->getDestinatairesNom()) ?></td>
        </tr>

        <?php $parcellesCommune++; $nomCommune = $parcelle->commune; $superficieCommune += $parcelle->superficie  ?>

    <?php endif; endforeach; ?>
        <tr class="total-commune">
            <td colspan="5" class="text-right">
                <strong>Total des <?php echo $parcellesCommune ?> parcelles de <?php echo $nomCommune ?></strong>
            </td>
            <td class="text-right"><strong><?php echoFloatFr($superficieCommune, 4) ?></strong></td>
        </tr>
        <tr class="vertical-center">
            <td colspan="5" style="text-align: right; font-weight: bold;">Surface affectable totale <?php echo ($nbParcelles > 1 )? "des $nbParcelles parcelles sélectionnées" : " de la parcelle sélectionnée"; ?></td>
            <td style="text-align: right; font-weight: bold;"><?php echoFloatFr($totalSurface,4); ?></td>
        </tr>
    </tbody>
</table>
<?php  endforeach; ?>
