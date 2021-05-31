<?php foreach ($parcellaireAffectation->declaration->getParcellesByDgc() as $dgc => $parcelles): ?>
<div class="row">
    <div class="col-xs-12">
        <h3>Dénomination complémentaire <?php echo str_replace("-", " ", $dgc); ?></h3>
    </div>
</div>
<table id="parcelles_<?php echo $commune; ?>" class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
    <thead>
        <tr>
        	<th class="col-xs-2">Commune</th>
            <th class="col-xs-2">Lieu-dit</th>
            <th class="col-xs-1">Section /<br />N° parcelle</th>
            <th class="col-xs-2">Cépage</th>
            <th class="col-xs-1">Année plantat°</th>
            <th class="col-xs-1" style="text-align: right;">Surf. affectable&nbsp;<span class="text-muted small">(ha)</span></th>
            <th class="col-xs-1">Affectation</th>
        </tr>
    </thead>
    <tbody>
    <?php
        $parcelles = $parcelles->getRawValue();
        ksort($parcelles);
        $nbParcelles = 0;
        $totalSurface = 0;
        foreach ($parcelles as $parcelle):
    ?><?php if($parcelle->affectee): $nbParcelles++; $totalSurface += round($parcelle->superficie_affectation,4) ?>
        <tr class="vertical-center">
            <td><?php echo $parcelle->commune; ?></td>
            <td><?php echo $parcelle->lieu; ?></td>
            <td style="text-align: center;"><?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?></td>
            <td><?php echo $parcelle->cepage; ?></td>
            <td><?php echo $parcelle->campagne_plantation; ?></td>
            <td style="text-align: right;"><span><?php echo number_format($parcelle->superficie_affectation,4); ?></span></td>
            <?php if($parcellaireAffectation->isValidee()): ?>
            <?php endif; ?>
            <td style="text-align: center;">
                    <?php if (round($parcelle->superficie_affectation,4) != round($parcelle->superficie,4)): ?>
                        <span>Partielle</span>
                    <?php else: ?><span>Totale</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endif; endforeach; ?>
        <tr class="vertical-center">
            <td colspan="5" style="text-align: right; font-weight: bold;">Surface affectable totale <?php echo ($nbParcelles > 1 )? "des $nbParcelles parcelles sélectionnées" : " de la parcelle sélectionnée"; ?></td>
            <td style="text-align: right; font-weight: bold;"><?php echo number_format($totalSurface,4); ?></td>
            <td></td>
        </tr>
    </tbody>
</table>
<?php  endforeach; ?>
