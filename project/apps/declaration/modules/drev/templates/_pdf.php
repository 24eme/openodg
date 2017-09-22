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
        <th class="th" style="text-align: left; width: 200px;">Appellation</th>
        <th class="th" style="text-align: center; width: 88px;">Superficie revendiquée</th>
        <th class="th" style="text-align: center; width: 88px;">Volume revendiqué sans VCI</th>
        <th class="th" style="text-align: center; width: 88px;">Volume revendiqué avec VCI</th>
        <th class="th" style="text-align: center; width: 88px;">VCI Constitué</th>
        <th class="th" style="text-align: center; width: 88px;">Stock VCI</th>
    </tr>
    <?php foreach($drev->declaration->getProduits(true) as $produit): ?>
        <tr>
            <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $produit->getLibelleComplet() ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->superficie_revendique) ?>&nbsp;<small>ha</small>&nbsp;&nbsp;&nbsp;</td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->volume_revendique_sans_vci) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->volume_revendique_avec_vci) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci): ?><?php echo sprintFloatFr($produit->vci) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci_stock_final): ?><?php echo sprintFloatFr($produit->vci_stock_final) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<br />
<div><span class="h3">&nbsp;Répartition du VCI 2015&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 200px;">Appellation</th>
        <th class="th" style="text-align: center; width: 88px;">Stock VCI 2015</th>
        <th class="th" style="text-align: center; width: 88px;">Complément</th>
        <th class="th" style="text-align: center; width: 88px;">Substitution</th>
        <th class="th" style="text-align: center; width: 88px;">A détruire</th>
        <th class="th" style="text-align: center; width: 88px;">Rafraichi</th>
    </tr>
    <?php foreach($drev->declaration->getProduits(true) as $produit): ?>
        <tr>
            <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $produit->getLibelleComplet() ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci_stock_initial): ?><?php echo sprintFloatFr($produit->vci_stock_initial) ?>&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci_complement_dr): ?><?php echo sprintFloatFr($produit->vci_complement_dr) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci_substitution): ?><?php echo sprintFloatFr($produit->vci_substitution) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci_destruction): ?><?php echo sprintFloatFr($produit->vci_destruction) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci_rafraichi): ?><?php echo sprintFloatFr($produit->vci_rafraichi) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if(DRevConfiguration::getInstance()->hasPrelevements()): ?>
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
<?php endif; ?>
