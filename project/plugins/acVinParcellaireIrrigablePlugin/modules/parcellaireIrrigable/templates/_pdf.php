<?php use_helper("Date"); ?>
<?php use_helper('ParcellaireAffectation') ?>
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
            <td style="width: 420px;">&nbsp;Nom : <i><?php echo $parcellaireIrrigable->declarant->raison_sociale ?></i></td>

            <td><?php if ($parcellaireIrrigable->declarant->cvi): ?>N° CVI : <i><?php echo $parcellaireIrrigable->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
            </tr>
            <tr>
                <td>&nbsp;Adresse : <i><?php echo $parcellaireIrrigable->declarant->adresse ?></i></td>
                <td>N° SIRET : <i><?php echo formatSIRET($parcellaireIrrigable->getDeclarantSiret()); ?></i></td>
            </tr>
            <tr>
                <td>&nbsp;Commune : <i><?php echo $parcellaireIrrigable->declarant->code_postal ?>, <?php echo $parcellaireIrrigable->declarant->commune ?></i></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Tél :<?php echo ($parcellaireIrrigable->declarant->telephone_bureau)? "&nbsp;<i>".$parcellaireIrrigable->declarant->telephone_bureau."</i>" : "" ?><?php
                echo ($parcellaireIrrigable->declarant->telephone_bureau && $parcellaireIrrigable->declarant->telephone_mobile)? "<i>/</i>" : "";
                echo ($parcellaireIrrigable->declarant->telephone_mobile)? "&nbsp;<i>".$parcellaireIrrigable->declarant->telephone_mobile."</i>" : "" ?> / Fax : <?php echo $parcellaireIrrigable->declarant->fax ?>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Email : <i><?php echo $parcellaireIrrigable->declarant->email ?></i></td>
                <td></td>
            </tr>
        </table>
</td></tr></table>

<?php if($parcellesByCommune === false): ?>
    <br />
    <br />
    <i>Aucune parcelle irrigable n'a été déclarée pour cette année en Côtes de Provence.</i>
    <br />
    <br />
    <?php return; ?>
<?php endif; ?>

<?php foreach($parcellesByCommune as $commune => $parcelles): ?>
<br />
<div><span class="h3">&nbsp;<?php echo $commune; ?>&nbsp;</span></div>

<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: center; width: 200px;">Lieu-dit</th>
        <th class="th" style="text-align: center; width: 70px;">Section</th>
        <th class="th" style="text-align: center; width: 70px;">N° parcelle</th>
        <th class="th" style="text-align: center; width: 180px;">Cépage</th>
        <th class="th" style="text-align: center; width: 80px;">Année de plantation</th>
        <th class="th" style="text-align: center; width: 80px;">Surface</th>
        <th class="th" style="text-align: center; width: 125px;">Type de matériel</th>
        <th class="th" style="text-align: center; width: 140px;">Type de ressource</th>
    </tr>
    <?php foreach ($parcelles as $parcelle): ?>
    	<tr>
			<td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->lieu; ?>&nbsp;</td>
			<td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->section; ?>&nbsp;</td>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->numero_parcelle; ?>&nbsp;</td>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->cepage; ?>&nbsp;</td>
            <td class="td" style="text-align: center;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->campagne_plantation; ?>&nbsp;</td>
            <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php printf("%0.4f", $parcelle->superficie); ?>&nbsp;<small>ha</small>&nbsp;</td>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->materiel; ?>&nbsp;</td>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->ressource; ?>&nbsp;</td>
    	</tr>
    <?php endforeach; ?>
</table>
<?php endforeach; ?>

<?php if($lastPage && $parcellaireIrrigable->observations): ?>
    <br />
    <div><span class="h3">&nbsp;Observations&nbsp;</span></div>
    <table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
        <tr>
            <td class="td"><?php echo tdStart() ?><?php echo nl2br($parcellaireIrrigable->observations); ?></td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($lastPage): ?>
    <small><br /></small>
    <i>Pour toute modification, contacter l'ODG.</i>
<?php endif; ?>
