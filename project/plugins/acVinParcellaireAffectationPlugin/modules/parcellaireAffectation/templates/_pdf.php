<?php use_helper("Date"); ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper('Compte') ?>
<style>
<?php echo styleParcellaire(); ?>
</style>

<div><span class="h3">&nbsp;Exploitation&nbsp;</span></div>
<table class="table"><tr><td>
    <table border="0">
        <tr>
            <td style="width: 420px;">&nbsp;Nom : <i><?php echo $parcellaireAffectation->declarant->raison_sociale ?></i></td>

            <td><?php if ($parcellaireAffectation->declarant->cvi): ?>N° CVI : <i><?php echo $parcellaireAffectation->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
            </tr>
            <tr>
                <td>&nbsp;Adresse : <i><?php echo $parcellaireAffectation->declarant->adresse ?></i></td>
                <td>N° SIRET : <i><?php echo formatSIRET($parcellaireAffectation->getDeclarantSiret()); ?></i></td>
            </tr>
            <tr>
                <td>&nbsp;Commune : <i><?php echo $parcellaireAffectation->declarant->code_postal ?>, <?php echo $parcellaireAffectation->declarant->commune ?></i></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Tél :<?php echo ($parcellaireAffectation->declarant->telephone_bureau)? "&nbsp;<i>".$parcellaireAffectation->declarant->telephone_bureau."</i>" : "" ?><?php
                echo ($parcellaireAffectation->declarant->telephone_bureau && $parcellaireAffectation->declarant->telephone_mobile)? "<i>/</i>" : "";
                echo ($parcellaireAffectation->declarant->telephone_mobile)? "&nbsp;<i>".$parcellaireAffectation->declarant->telephone_mobile."</i>" : "" ?> / Fax : <?php echo $parcellaireAffectation->declarant->fax ?>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Email : <i><?php echo $parcellaireAffectation->declarant->email ?></i></td>
                <td></td>
            </tr>
        </table>
</td></tr></table>

<?php if($parcellesByCommune === false): ?>
    <br />
    <br />
    <i>Aucune parcelle affectée n'a été déclarée pour cette année.</i>
    <br />
    <br />
    <?php return; ?>
<?php endif; ?>

<?php foreach($parcellesByCommune as $group => $parcelles): ?>
<br />
<div><span class="h3">&nbsp;<?php if ($parcellaireAffectation->hasDgc()): ?>Dénomination <?php endif; ?><?php echo $group; ?>&nbsp;</span></div>

<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
    	<th class="th" style="text-align: center; width: 200px;">Commune</th>
        <th class="th" style="text-align: center; width: 200px;">Lieu-dit</th>
        <th class="th" style="text-align: center; width: 80px;">Section</th>
        <th class="th" style="text-align: center; width: 80px;">N° p<sup>lle</sup></th>
        <th class="th" style="text-align: center; width: 180px;">Cépage</th>
        <th class="th" style="text-align: center; width: 100px;">Année de plantation</th>
        <th class="th" style="text-align: center; width: 100px;">Surface</th>
    </tr>
    <?php
        $total_produit = 0;
        foreach ($parcelles as $parcelle):
            if(!$parcelle->affectee): continue; endif;
            $total_produit += $parcelle->superficie;
         ?>
    	<tr>
    		<td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->commune; ?>&nbsp;</td>
			<td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->lieu; ?>&nbsp;</td>
			<td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->section; ?>&nbsp;</td>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->numero_parcelle; ?>&nbsp;</td>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->cepage; ?>&nbsp;</td>
            <td class="td" style="text-align: center;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->campagne_plantation; ?>&nbsp;</td>
            <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php printf("%0.4f", $parcelle->superficie); ?>&nbsp;<small>ha</small>&nbsp;</td>
    	</tr>
    <?php endforeach; ?>
    <tr>
        <td class="td" colspan="6" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<strong>Total</strong>&nbsp;</td>
        <td class="td" colspan="1" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<strong><?php printf("%0.4f", $total_produit); ?>&nbsp;<small>ha</small></strong>&nbsp;</td>
    </tr>
</table>
<?php endforeach; ?>

<?php if($lastPage): ?>
<table border="0" cellspacing=0 cellpadding=0>
    <tr>
        <td style="width: 485px;">
            <?php $synthese = $parcellaireAffectation->getSyntheseCepages(); ?>
            <br />
            <div><span class="h3">&nbsp;Synthèse par cépages&nbsp;</span></div>
            <table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
                <tr>
                    <th class="th" style="text-align: left; width: 330px;">&nbsp;Cépage</th>
                    <th class="th" style="text-align: center; width: 130px;">Surface</th>
                </tr>
                <?php foreach($synthese as $cepage_libelle => $s): ?>
                    <tr>
                        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $cepage_libelle; ?>&nbsp;</td>
                        <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php printf("%0.4f", $s['superficie']); ?>&nbsp;<small>ha</small>&nbsp; &nbsp;</td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<strong>Total</strong>&nbsp;</td>
                    <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<strong><?php printf("%0.4f", array_sum(array_column($synthese->getRawValue(), 'superficie'))); ?>&nbsp;<small>ha</small></strong>&nbsp; &nbsp;</td>
                </tr>
            </table>
        </td>
        <td>
            <?php $syntheseDestination = $parcellaireAffectation->getSyntheseDestination(); ?>
            <?php if (count($syntheseDestination)): ?>
            <div><span class="h3">&nbsp;Synthèse par destinations&nbsp;</span></div>
            <table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
                <tr>
                    <th class="th" style="text-align: left; width: 330px;">&nbsp;Destination</th>
                    <th class="th" style="text-align: center; width: 130px;">Surface</th>
                </tr>
                <?php foreach($syntheseDestination as $destination => $s): ?>
                    <tr>
                        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $destination; ?>&nbsp;</td>
                        <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php printf("%0.4f", $s); ?>&nbsp;<small>ha</small>&nbsp; &nbsp;</td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<strong>Total</strong>&nbsp;</td>
                    <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<strong><?php printf("%0.4f", array_sum($syntheseDestination->getRawValue())); ?>&nbsp;<small>ha</small></strong>&nbsp; &nbsp;</td>
                </tr>
            </table>
            <?php endif; ?>
        </td>
    </tr>
</table>
<?php endif; ?>

<?php if($lastPage && $parcellaireAffectation->observations): ?>
    <br />
    <div><span class="h3">&nbsp;Observations&nbsp;</span></div>
    <table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
        <tr>
            <td class="td"><?php echo tdStart() ?><?php echo nl2br($parcellaireAffectation->observations); ?></td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($lastPage): ?>
    <small><br /></small>
    <i>Pour toute modification, contacter l'ODG.</i>
<?php endif; ?>
