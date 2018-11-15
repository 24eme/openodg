<?php use_helper('Float') ?>
<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<div class="page-header">
    <h2>Revendication</h2>
</div>

<?php include_component('drev', 'stepRevendication', array('drev' => $drev, 'step' => 'recapitulatif')) ?>

<?php if($isBlocked): ?>
<div class="alert alert-danger">
Vous devez déclarer vos volumes revendiqués par cépage pour pouvoir continuer
</div>
<?php endif; ?>

<?php if(count($drev->getProduits(true)) > 0): ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th class="col-xs-6">Appellation revendiquée</th>
                <?php if (count($drev->getProduitsVCI()) > 0): ?>
                	<th class="text-center col-xs-2">Superficie&nbsp;vinififée</th>
                	<th class="text-center col-xs-2">VCI&nbsp;constitué</th>
                	<th class="text-center col-xs-2">Volume&nbsp;revendiqué</th>
                <?php else: ?>
                	<th class="text-center col-xs-3">Superficie&nbsp;vinifiée</th>
                	<th class="text-center col-xs-3">Volume&nbsp;revendiqué</th>
                <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($drev->getProduits(true) as $key => $produit) : ?>
            <?php if($produit->volume_revendique > 0): ?>
            <tr>
                <td><?php echo $produit->getLibelleComplet() ?> <?php if ($produit->canHaveVtsgn()):?><small class="text-muted">(hors VT/SGN)</small><?php endif; ?></td>
                <td class="text-center"><?php if($produit->exist('superficie_vinifiee')): ?><?php echoFloat($produit->superficie_vinifiee) ?> <small class="text-muted">ares</small><?php endif; ?></td>
                <?php if (count($drev->getProduitsVCI()) > 0): ?>
                <td  class="text-center"><?php if ($produit->getTotalConstitue() > 0): ?><?php echoFloat($produit->getTotalConstitue()) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                <?php endif; ?>
                <td class="text-center"><?php echoFloat($produit->volume_revendique) ?> <small class="text-muted">hl</small></td>
            </tr>
            <?php endif; ?>
            <?php if($produit->canHaveVtsgn() && $produit->volume_revendique_vtsgn > 0): ?>
            <tr>
                <td><?php echo $produit->getLibelleComplet() ?> VT/SGN</td>
                <td class="text-center"><?php if($produit->exist('superficie_vinifiee_vtsgn')): ?><?php echoFloat($produit->superficie_vinifiee_vtsgn) ?> <small class="text-muted">ares</small><?php endif; ?></td>
                <?php if (count($drev->getProduitsVCI()) > 0): ?>
                <td></td>
                <?php endif; ?>
                <td class="text-center"><?php echoFloat($produit->volume_revendique_vtsgn) ?> <small class="text-muted">hl</small></td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="row row-margin row-button">
    <div class="col-xs-6">
    	<?php if($drev->hasProduitsVCI()): ?>
    	<a href="<?php echo url_for("drev_revendication_cepage_vci", $drev) ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'utilisation VCI</small></a>
    	<?php else: ?>
    	<a href="<?php echo url_for("drev_revendication_cepage", $drev->declaration->getAppellations()->getLast()) ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'appellation précédente</small></a>
    	<?php endif; ?>
    </div>
    <div class="col-xs-6 text-right">
        <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
            <button <?php if($isBlocked): ?>disabled="disabled"<?php endif; ?>  id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
            <?php else: ?>
            <a <?php if($isBlocked): ?>disabled="disabled"<?php endif; ?> href="<?php echo url_for("drev_degustation_conseil", $drev)?>" class="btn btn-default btn-lg btn-upper">Continuer <small>vers la dégustation conseil</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></a>
        <?php endif; ?>
    </div>
</div>
</form>
