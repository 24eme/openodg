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
            <td style="width: 420px;">&nbsp;Nom : <i><?php echo $parcellaireManquant->declarant->raison_sociale ?></i></td>

            <td><?php if ($parcellaireManquant->declarant->cvi): ?>N° CVI : <i><?php echo $parcellaireManquant->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
            </tr>
            <tr>
                <td>&nbsp;Adresse : <i><?php echo $parcellaireManquant->declarant->adresse ?></i></td>
                <td>N° SIRET : <i><?php echo formatSIRET($parcellaireManquant->getDeclarantSiret()); ?></i></td>
            </tr>
            <tr>
                <td>&nbsp;Commune : <i><?php echo $parcellaireManquant->declarant->code_postal ?>, <?php echo $parcellaireManquant->declarant->commune ?></i></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Tél :<?php echo ($parcellaireManquant->declarant->telephone_bureau)? "&nbsp;<i>".$parcellaireManquant->declarant->telephone_bureau."</i>" : "" ?><?php
                echo ($parcellaireManquant->declarant->telephone_bureau && $parcellaireManquant->declarant->telephone_mobile)? "<i>/</i>" : "";
                echo ($parcellaireManquant->declarant->telephone_mobile)? "&nbsp;<i>".$parcellaireManquant->declarant->telephone_mobile."</i>" : "" ?> / Fax : <?php echo $parcellaireManquant->declarant->fax ?>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Email : <i><?php echo $parcellaireManquant->declarant->email ?></i></td>
                <td></td>
            </tr>
        </table>
</td></tr></table>

<?php if($parcellesByCommune === false): ?>
    <br />
    <br />
    <i>Aucune parcelle dont le taux de pieds morts ou manquants est supérieur à <?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>%.</i>
    <br />
    <br />
    <?php return; ?>
<?php endif; ?>

<?php
    $nbparcelles = 0;
    foreach($parcellesByCommune as $commune => $parcelles):
    $nouvellecommeune = true;
    foreach ($parcelles as $parcelle):
    if ($nouvellecommeune || ($nbparcelles % 14 < 1) ):
        $nouvellecommeune = false ;
?>
        <?php if ($nbparcelles): ?>
        </table>
        <?php endif; ?>
        <br />
        <div><span class="h3">&nbsp;<?php echo $commune; ?>&nbsp;</span></div>
        <table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
            <tr>
                <th class="th" style="text-align: center; width: 140px;">Lieu-dit</th>
                <th class="th" style="text-align: center; width: 55px;">Section</th>
                <th class="th" style="text-align: center; width: 60px;">N° parcelle</th>
                <th class="th" style="text-align: center; width: 365px;">Produit</th>
                <th class="th" style="text-align: center; width: 85px;">Année de plantation</th>
                <th class="th" style="text-align: center; width: 80px;">Surface</th>
                <th class="th" style="text-align: center; width: 80px;">Densité</th>
                <th class="th" style="text-align: center; width: 80px;">% de pieds manquants</th>
            </tr>
<?php $nbparcelles += 2; //Incrément de l'enteête ?>
<?php endif; ?>
    	<tr>
			<td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->lieu; ?>&nbsp;</td>
			<td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->section; ?>&nbsp;</td>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->numero_parcelle; ?>&nbsp;</td>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->produit->libelle ?>&nbsp;<?php echo $parcelle->cepage; ?>&nbsp;</td>
            <td class="td" style="text-align: center;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->campagne_plantation; ?>&nbsp;</td>
            <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php echo sprintFloatFr($parcelle->superficie, 4); ?>&nbsp;<small>ha</small>&nbsp;</td>
            <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->densite; ?>&nbsp;</td>
            <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php echo sprintFloatFr($parcelle->pourcentage); ?>&nbsp;%</td>
    	</tr>
<?php $nbparcelles++; ?>
<?php endforeach; ?>
<?php endforeach; ?>
</table>
<?php if($lastPage && $parcellaireManquant->observations): ?>
    <br />
    <div><span class="h3">&nbsp;Observations&nbsp;</span></div>
    <table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
        <tr>
            <td class="td"><?php echo tdStart() ?><?php echo nl2br($parcellaireManquant->observations); ?></td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($lastPage): ?>
    <small><br /></small>
    <i>Pour toute modification, contacter l'ODG.</i>
<?php endif; ?>
