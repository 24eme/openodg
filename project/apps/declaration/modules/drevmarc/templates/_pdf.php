<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper("Date"); ?>
<style>
<?php echo style(); ?>
</style>

<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
<table border="0">
    <tr>
        <td style="width: 420px;">&nbsp;Nom : <i><?php echo $drevmarc->declarant->raison_sociale ?></i></td>
        <td>N° CVI : <i><?php echo $drevmarc->declarant->cvi ?></i></td>
    </tr>
    <tr>
        <td>&nbsp;Adresse : <i><?php echo $drevmarc->declarant->adresse ?></i></td>
        <td>SIRET : <i><?php echo $drevmarc->declarant->siret ?></i></td>
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
<div><span class="h3">&nbsp;Revendication&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 357px">&nbsp;Période de distillation</th>
        <td style="text-align: center; width: 280px"><?php echo 'Du '.$drevmarc->debut_distillation.' au '.$drevmarc->fin_distillation ?></td>
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 357px">&nbsp;Quantité de marc mis en oeuvre en kg</th>
        <td style="text-align: center; width: 280px"><?php echo $drevmarc->qte_marc; ?></td>
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 357px">&nbsp;Volume total obtenu en hl d'alcool pur</th>
        <td style="text-align: center; width: 280px"><?php echo $drevmarc->volume_obtenu; ?></td>
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 357px">&nbsp;Titre alcoométrique volumique</th>
        <td style="text-align: center; width: 280px"><?php echo $drevmarc->titre_alcool_vol; ?></td>
    </tr>
</table>
<br />
<br />
<p>Signé éléctroniquement <i>via l'application de télédéclaration le 08/06/2014</i></p>