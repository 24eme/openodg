<?php use_helper("Date"); ?>
<?php use_helper('DRevMarc') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<style>
<?php echo styleDRevMarc(); ?>
</style>

<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
            <table border="0">
                <tr>
                    <td style="width: 420px;">&nbsp;Nom : <i><?php echo $drevmarc->declarant->raison_sociale ?></i></td>
                    
                    <td><?php if($drevmarc->declarant->cvi): ?>N° CVI : <i><?php echo $drevmarc->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
                </tr>
                <tr>
                    <td>&nbsp;Adresse : <i><?php echo $drevmarc->declarant->adresse ?></i></td>
                    <td>N° SIRET : <i><?php echo $drevmarc->declarant->siret ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Commune : <i><?php echo $drevmarc->declarant->code_postal ?>, <?php echo $drevmarc->declarant->commune ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Tel / Fax : <i><?php echo $drevmarc->declarant->telephone ?> / <?php echo $drevmarc->declarant->fax ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Email : <i><?php echo $drevmarc->declarant->email ?></i></td>
                    <td></td>
                </tr>
            </table>
        </td></tr></table>
<br />
<br />
<br />
<div><span class="h3">&nbsp;Période de distillation&nbsp;</span></div>
<table class="table" border="1" cellspacing=2 cellpadding=0 style="text-align: right;">   
    <tr>
        <th class="td" style="border-top: none; text-align: center; width: 637px; vertical-align: middle;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;Du&nbsp;<?php echo format_date($drevmarc->debut_distillation, "D", "fr_FR"); ?>&nbsp;au&nbsp;<?php echo format_date($drevmarc->fin_distillation, "D", "fr_FR"); ?>&nbsp;<?php echo tdStart() ?></th>
    </tr>
</table>
<br />
<br />

<div><span class="h3">&nbsp;Revendication&nbsp;</span></div>
<table class="table" border="1" cellspacing=2 cellpadding=0 style="text-align: right;">   
    <tr>
        <th class="th" style="border-top: none; text-align: left; width: 357px; vertical-align: middle;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;Quantité de marc mis en oeuvre<?php echo tdStart() ?></th>
        <td class="td" style="border-top: none; text-align: center; width: 280px; vertical-align: middle;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;<?php echo getQtemarc($drevmarc); ?><?php echo tdStart() ?></td>
    </tr>
    <tr>
        <th class="th" style="border-top: none; text-align: left; width: 357px; vertical-align: middle;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;Volume total obtenu<?php echo tdStart() ?></th>
        <td class="td" style="border-top: none; text-align: center; width: 280px; vertical-align: middle;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;<?php echo getVolumeObtenu($drevmarc); ?><?php echo tdStart() ?></td>
    </tr>
    <tr>
        <th class="th" style="border-top: none;  text-align: left; width: 357px; vertical-align: middle;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;Titre alcoométrique volumique<?php echo tdStart() ?></th>
        <td class="td" style="border-top: none;  text-align: center; width: 280px; vertical-align: middle;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;<?php echo getTitreAlcoolVol($drevmarc); ?><?php echo tdStart() ?></td>
    </tr>
</table>
<br />
<br />