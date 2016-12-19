<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper("Date"); ?>
<style>
<?php echo styleDRev(); ?>
</style>

<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
<table border="0">
    <tr>
        <td style="width: 420px;">&nbsp;Nom : <i><?php echo $drev->declarant->raison_sociale ?></i></td>
        <td>N° CVI : <i><?php echo $drev->declarant->cvi ?></i></td>
    </tr>
    <tr>
        <td>&nbsp;Adresse : <i><?php echo $drev->declarant->adresse ?></i></td>
        <td>SIRET : <i><?php echo $drev->declarant->siret ?></i></td>
    </tr>
    <tr>
        <td>&nbsp;Commune : <i><?php echo $drev->declarant->code_postal ?> <?php echo $drev->declarant->commune ?></i></td>
        <td></td>
    </tr>
    <tr>
        <td>&nbsp;Tel / Fax : <i><?php echo $drev->declarant->telephone ?> / <?php echo $drev->declarant->fax ?></i></td>
        <td></td>
    </tr>
    <tr>
        <td>&nbsp;Email : <i><?php echo $drev->declarant->email ?></i></td>
        <td></td>
    </tr>
</table>
</td></tr></table>
<br />
<div><span class="h3">&nbsp;Revendication&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; <?php if(!$drev->isNonRecoltant()): ?>width: 357px;<?php else: ?>width: 467px;<?php endif; ?>">&nbsp;Appellation</th>
        <?php if(!$drev->isNonRecoltant()): ?>
        <th class="th" style="text-align: center; width: 140px;">Superficie</th>
        <?php endif; ?>
        <th class="th" style="text-align: center; <?php if(!$drev->isNonRecoltant()): ?>width: 140px;<?php else: ?>width: 170px;<?php endif; ?>">Volume</th>
    </tr>
    <?php foreach($drev->declaration->getProduits(true) as $produit): ?>
        <?php if($produit->volume_revendique|| $produit->superficie_revendique): ?>
            <tr>
                <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $produit->getLibelleComplet() ?><?php if($produit->canHaveVtsgn()): ?> <small>(hors VT/SGN)</small><?php endif; ?></td>
                <?php if(!$drev->isNonRecoltant()): ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->superficie_revendique) ?>&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
                <?php endif; ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->volume_revendique) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
            </tr>
        <?php endif; ?>
        <?php if($produit->canHaveVtsgn() && ($produit->volume_revendique_vtsgn || $produit->superficie_revendique_vtsgn)): ?>
            <tr>
                <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $produit->getLibelleComplet() ?> VT/SGN</td>
                <?php if(!$drev->isNonRecoltant()): ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->superficie_revendique_vtsgn) ?>&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
                <?php endif; ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->volume_revendique_vtsgn) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
            </tr>
        <?php endif; ?>
    <?php  endforeach; ?>
</table>
<br />
<br />
<br />
<table cellspacing=0 cellpadding=0>
<tr><td class="tdH2Big"><span class="h2">Dégustation conseil</span></td></tr>
</table>
<?php include_partial('drev/pdfPrelevements', array('drev' => $drev, 'type' => DRev::CUVE)); ?>
<br />
<br />
<?php if(count($drev->getPrelevementsByDate(DRev::BOUTEILLE)) > 0 || $drev->isNonConditionneurJustForThisMillesime()): ?>
<br />
<table cellspacing=0 cellpadding=0>
<tr><td class="tdH2Big"><span class="h2">Contrôle externe</span></td></tr>
</table>
<?php if(count($drev->getPrelevementsByDate(DRev::BOUTEILLE)) > 0): ?>
<?php include_partial('drev/pdfPrelevements', array('drev' => $drev, 'type' => DRev::BOUTEILLE)); ?>
<?php elseif($drev->isNonConditionneurJustForThisMillesime()): ?>
<em>Ne conditionne pas de volume pour ce millésime.</em>
<?php endif; ?>
<?php endif; ?>
