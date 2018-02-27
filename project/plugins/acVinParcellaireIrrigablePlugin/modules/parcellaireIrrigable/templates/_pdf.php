<?php use_helper("Date"); ?>
<?php use_helper('Parcellaire') ?>
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
                    <td>N° SIRET : <i><?php echo formatSIRET($parcellaireIrrigable->declarant->siret); ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Commune : <i><?php echo $parcellaireIrrigable->declarant->code_postal ?>, <?php echo $parcellaireIrrigable->declarant->commune ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Tel / Fax : <i><?php echo $parcellaireIrrigable->declarant->telephone ?> / <?php echo $parcellaireIrrigable->declarant->fax ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Email : <i><?php echo $parcellaireIrrigable->declarant->email ?></i></td>
                    <td></td>
                </tr>
            </table>
        </td></tr></table>
<br /> 

<div><span class="h3">&nbsp;<?php echo $titre; ?>&nbsp;</span></div>

<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: center; width: 240px;">Parcelle</th>        
        <th class="th" style="text-align: center; width: 260px;">Cépages</th>       
        <th class="th" style="text-align: center; width: 110px;">Matériel</th>
        <th class="th" style="text-align: center; width: 110px;">Ressource</th> 
        <th class="th" style="text-align: center; width: 225px;">Observations</th>
    </tr>
    <?php foreach ($parcellesForDetail as $parcelle): ?>
    	<tr>
			<td class="td" style="text-align: left;"><?php echo $parcelle->getIdentificationParcelleLibelle(ESC_RAW); ?></td>
			<td class="td" style="text-align: left;"><?php echo $parcelle->getIdentificationCepageLibelle(ESC_RAW); ?></td>
            <td class="td" style="text-align: left;"><?php echo $parcelle->materiel; ?></td>
            <td class="td" style="text-align: left;"><?php echo $parcelle->ressource; ?></td>
            <td class="td" style="text-align: left;"><?php echo $parcelle->observations; ?></td>
    	</tr>
    <?php endforeach; ?>

</table>

<br />
<br />
