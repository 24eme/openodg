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
        <th class="th" style="text-align: left; <?php if ($drev->canHaveSuperficieVinifiee()): ?><?php if(!$drev->isNonRecoltant()): ?><?php if($drev->declaration->hasVolumeRevendiqueVci()): ?>width: 240px;<?php else: ?>width: 340px;<?php endif; ?><?php else: ?><?php if($drev->declaration->hasVolumeRevendiqueVci()): ?>width: 340px;<?php else: ?>width: 440px;<?php endif; ?><?php endif; ?><?php else: ?><?php if(!$drev->isNonRecoltant()): ?><?php if($drev->declaration->hasVolumeRevendiqueVci()): ?>width: 340px;<?php else: ?>width: 440px;<?php endif; ?><?php else: ?><?php if($drev->declaration->hasVolumeRevendiqueVci()): ?>width: 420px;<?php else: ?>width: 520px;<?php endif; ?><?php endif; ?><?php endif; ?>">&nbsp;Appellation</th>
        <?php if(!$drev->isNonRecoltant()): ?>
        <th class="th" style="text-align: center; width: 100px;">Superficie<br />totale</th>
        <?php endif; ?>
        <?php if ($drev->canHaveSuperficieVinifiee()): ?>
        <th class="th" style="text-align: center; width: 100px;">Superficie<br />vinifiée</th>
        <?php endif; ?>
        <?php if($drev->declaration->hasVolumeRevendiqueVci()): ?>
        <th class="th" style="text-align: center; width: 100px;">Volume<br />issu du VCI</th>
        <?php endif ?>
        <th class="th" style="text-align: center; width: 100px;">Volume<br />revendiqué</th>
    </tr>
    <?php foreach($drev->declaration->getProduits(true) as $produit): ?>
        <?php if($produit->volume_revendique|| $produit->superficie_revendique || ($produit->exist('superficie_vinifiee') && $produit->superficie_vinifiee)): ?>
            <tr>
                <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $produit->getLibelleComplet() ?><?php if($produit->canHaveVtsgn()): ?> <small>(hors VT/SGN)</small><?php endif; ?></td>
                <?php if(!$drev->isNonRecoltant()): ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->superficie_revendique) ?>&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
                <?php endif; ?>
                <?php if ($drev->canHaveSuperficieVinifiee()): ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->exist('superficie_vinifiee')): ?><?php echo sprintFloatFr($produit->superficie_vinifiee) ?>&nbsp;<small>ares</small>&nbsp;&nbsp;<?php endif; ?>&nbsp;</td>
                <?php endif; ?>
			    <?php if($produit->hasVolumeRevendiqueVci()): ?>
			    <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->exist('volume_revendique_vci')): ?><?php echo sprintFloatFr($produit->get('volume_revendique_vci')) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;<?php endif; ?>&nbsp;</td>
			    <?php elseif($drev->declaration->hasVolumeRevendiqueVci()): ?>
			    <td class="td" style="text-align:right;">&nbsp;</td>
			    <?php endif; ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->volume_revendique) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
            </tr>
        <?php endif; ?>
        <?php if($produit->canHaveVtsgn() && ($produit->volume_revendique_vtsgn || $produit->superficie_revendique_vtsgn || ($produit->exist('superficie_vinifiee_vtsgn') && $produit->superficie_vinifiee_vtsgn))): ?>
            <tr>
                <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $produit->getLibelleComplet() ?> VT/SGN</td>
                <?php if(!$drev->isNonRecoltant()): ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->superficie_revendique_vtsgn) ?>&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
                <?php endif; ?>
                <?php if ($drev->canHaveSuperficieVinifiee()): ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->exist('superficie_vinifiee_vtsgn')): ?><?php echo sprintFloatFr($produit->superficie_vinifiee_vtsgn) ?>&nbsp;<small>ares</small>&nbsp;&nbsp;<?php endif; ?>&nbsp;</td>
                <?php endif; ?>
                <?php if($drev->declaration->hasVolumeRevendiqueVci()): ?>
			    <td class="td" style="text-align:right;">&nbsp;</td>
			    <?php endif; ?>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->volume_revendique_vtsgn) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
            </tr>
        <?php endif; ?>
    <?php  endforeach; ?>
</table>
<br />

<?php if($drev->declaration->hasVolumeRevendiqueVci()): ?>
<div><span class="h3">&nbsp;Revendication VCI&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
    	<th class="th" style="text-align: center; width: 240px;">Appellation</th>
        <th class="th" style="text-align: center; width: 100px;">Destruction</th>
        <th class="th" style="text-align: center; width: 100px;">Complément de la récolte</th>
        <th class="th" style="text-align: center; width: 100px;">Substitution</th>
        <th class="th" style="text-align: center; width: 100px;">Rafraichissement</th>
    </tr>
    <?php foreach ($drev->getProduitsVci() as $key => $produit): ?>
            <tr>
                <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $produit->getLibelleComplet() ?><br /><small class="text-muted">&nbsp;<?php echo $produit->stockage_libelle ?></small></td>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->destruction) ?><?php if (!is_null($produit->destruction)): ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->complement) ?><?php if (!is_null($produit->complement)): ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->substitution) ?><?php if (!is_null($produit->substitution)): ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
                <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php echo sprintFloatFr($produit->rafraichi) ?><?php if (!is_null($produit->rafraichi)): ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            </tr>
    <?php  endforeach; ?>
</table>
<br />
<?php endif; ?>

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
