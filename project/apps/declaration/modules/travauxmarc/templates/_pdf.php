<?php use_helper("Date"); ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<style>
<?php echo styleTravauxMarc(); ?>
</style>

<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt">
    <tr>
        <td>
            <table border="0">
            <tr>
                <td style="width: 420px;">&nbsp;Nom : <i><?php echo $travauxmarc->declarant->raison_sociale ?></i></td>

                <td><?php if($travauxmarc->declarant->cvi): ?>N° CVI : <i><?php echo $travauxmarc->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
            </tr>
            <tr>
                <td>&nbsp;Adresse : <i><?php echo $travauxmarc->declarant->adresse ?></i></td>
                <td>N° SIRET : <i><?php echo $travauxmarc->declarant->siret ?></i></td>
            </tr>
            <tr>
                <td>&nbsp;Commune : <i><?php echo $travauxmarc->declarant->code_postal ?> <?php echo $travauxmarc->declarant->commune ?></i></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Tel / Fax : <i><?php echo $travauxmarc->declarant->telephone ?> / <?php echo $travauxmarc->declarant->fax ?></i></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Email : <i><?php echo $travauxmarc->declarant->email ?></i></td>
                <td></td>
            </tr>
            </table>
        </td>
    </tr>
</table>
<br />
<br />
<div><span class="h3">&nbsp;Distillation&nbsp;</span></div>
<table class="table" border="0" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <td class="th" style="text-align: left; width: 300px;"><?php echo tdStart() ?>&nbsp;Date de distillation</td>
        <td class="td" style="text-align: left; width: 337px;"><?php echo tdStart() ?>&nbsp;<?php echo $travauxmarc->getDateDistillationFr(); ?></td>
    </tr>
    <tr>
        <td class="th" style="text-align: left;"><?php echo tdStart() ?>&nbsp;Distillation par un prestataire</td>
        <td class="td"><?php echo tdStart() ?>&nbsp;<?php if($travauxmarc->distillation_prestataire): ?>Oui<?php else: ?>Non<?php endif; ?></td>
    </tr>
    <tr>
        <td class="th" style="text-align: left;"><?php echo tdStart() ?>&nbsp;L'alambic utilisé est celui décrit dans la déclaration<br />&nbsp;d'identification</td>
        <td class="td"><?php echo tdStart() ?>&nbsp;<?php if($travauxmarc->alambic_connu): ?>Oui<?php else: ?>Non<?php endif; ?></td>
    </tr>
    <tr>
        <td class="th" style="text-align: left;"><?php echo tdStart() ?>&nbsp;Adresse de distillation</td>
        <td class="td"><?php echo tdStart() ?>&nbsp;<?php echo $travauxmarc->adresse_distillation->adresse ?><br />&nbsp;<?php echo $travauxmarc->adresse_distillation->code_postal ?>&nbsp;<?php echo $travauxmarc->adresse_distillation->commune ?></td>
    </tr>
</table>
<br />
<div><span class="h3">&nbsp;Fournisseur de marcs&nbsp;</span></div>
<table class="table" border="0" cellspacing=0 cellpadding=0 style="text-align: left;">
    <tr>
        <th class="th" style="width: 409px;">Nom</th>
        <th class="th" style="width: 114px; text-align: center;">Date de livraison</th>
        <th class="th" style="width: 114px; text-align: center;">Quantité de marcs<br /><small>(en kg)</small></th>
    </tr>
    <?php foreach ($travauxmarc->fournisseurs as $fournisseur): ?>
    <tr>
        <td class="td"><?php echo tdStart() ?>&nbsp;<?php echo $fournisseur->nom; ?></td>
        <td class="td" style="text-align: center;"><?php echo tdStart() ?>&nbsp;<?php echo $fournisseur->getDateLivraisonFr(); ?></td>
        <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echoFloat($fournisseur->quantite); ?>&nbsp;<small>kg</small>&nbsp;</td>
    </tr>
    <?php endforeach; ?>
</table>
