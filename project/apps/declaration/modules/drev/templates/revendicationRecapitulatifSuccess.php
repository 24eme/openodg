<?php use_helper('Float') ?>

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
            <th class="col-xs-8">Appellation revendiquée</th>
            <th class="col-xs-4 text-center">Volume&nbsp;revendiqué</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($drev->getProduits(true) as $key => $produit) : ?>
            <?php if($produit->volume_revendique > 0): ?>
            <tr>
                <td><?php echo $produit->getLibelleComplet() ?> <small class="text-muted">(hors VT/SGN)</small></td>
                <td class="text-center"><?php echoFloat($produit->volume_revendique) ?> <small class="text-muted">hl</small></td>
            </tr>
            <?php endif; ?>
            <?php if($produit->canHaveVtsgn() && $produit->volume_revendique_vtsgn > 0): ?>
            <tr>
                <td><?php echo $produit->getLibelleComplet() ?> VT/SGN</td>
                <td class="text-center"><?php echoFloat($produit->volume_revendique_vtsgn) ?> <small class="text-muted">hl</small></td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("drev_revendication_cepage", $drev->declaration->getAppellations()->getLast()) ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'appellation précédente</small></a></div>
    <div class="col-xs-6 text-right">
        <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
            <button <?php if($isBlocked): ?>disabled="disabled"<?php endif; ?>  id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
            <?php else: ?>
            <a <?php if($isBlocked): ?>disabled="disabled"<?php endif; ?> href="<?php echo url_for("drev_degustation_conseil", $drev)?>" class="btn btn-default btn-lg btn-upper">Continuer <small>vers la dégustation conseil</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></a>
        <?php endif; ?>
    </div>
</div>
</form>
