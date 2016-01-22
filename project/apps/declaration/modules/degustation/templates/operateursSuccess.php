<?php use_javascript("degustation.js?201601221219", "last") ?>
<?php use_helper("Date") ?>

<?php include_partial('degustation/step', array('tournee' => $tournee, 'active' => TourneeEtapes::ETAPE_OPERATEURS)); ?>

<div class="page-header">
    <h2>Choix des Opérateurs</h2>
</div>

<form id="form_degustation_choix_operateurs" action="<?php echo url_for('degustation_operateurs', $tournee) ?>" method="post" class="form-horizontal ajaxForm">
<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>
<input type="hidden" id="nb_a_prelever" value="<?php echo $nb_a_prelever ?>"/>

<div class="row">
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div class="btn-group">
            <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge"><?php echo count($tournee->operateurs) ?></span></a>
            <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">À prélever <span class="badge"><?php echo count($tournee->getOperateursPrelevement()) ?></span></a>
        </div>
        <span class="pull-right lead text-muted"><span data-dynamic-value="nb-lots"><?php echo $tournee->getNbLots() ?></span> lot(s)</span>
    </div>
    <div class="col-xs-12">
        <div id="listes_operateurs" class="list-group">
            <?php foreach($form as $key => $field): ?>
                <?php if($field->isHidden()): continue; endif; ?>
                <?php $operateur = $tournee->operateurs->get($key); ?>
                <?php $exist = count($operateur->getLotsPrelevement()) > 0; ?>
                <div <?php if($exist): ?>data-state="active"<?php endif; ?> class="list-group-item list-group-item-item col-xs-12 <?php if(!$exist): ?>clickable<?php else: ?>list-group-item-success<?php endif; ?>">
                <div class="row">
                    <div class="col-xs-6"><?php echo $operateur->raison_sociale ?> <small>(<?php echo $operateur->cvi ?>)</small> <small class="text-muted"><?php echo $operateur->commune ?></small></div>
                    <div class="col-xs-2 text-right"><!--<small class="text-muted">Prélevé le</small> 2012, 2014--> <?php if($operateur->reporte): ?><span class="label label-warning">Report du <?php echo format_date($operateur->reporte, "D", "fr_FR") ?></span><?php elseif($derniereDegustation=$operateur->getLastDegustationDate()): ?><span class="label label-info">Dégusté en <?php echo format_date($derniereDegustation, "yyyy   ", "fr_FR") ?></span><?php endif; ?></div>
                    <div class="col-xs-3 text-right">
                        <small class="text-muted">Pour le </small> <?php echo format_date($operateur->date_demande, "D", "fr_FR") ?>
                    </div>
                    <div class="col-xs-1">
                        <button class="btn btn-success btn-sm pull-right <?php if($exist): ?>hidden<?php endif; ?>" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                        <button class="btn btn-danger btn-sm pull-right <?php if(!$exist): ?>hidden<?php endif; ?>" style="opacity: 0.7;" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                    </div>
                    <div class="col-xs-12">
                        <div class="btn-group select" <?php if(!$exist): ?>disabled="disabled"<?php endif; ?> data-selection-mode="<?php echo ($tournee->appellation == 'VTSGN') ? "all" : "auto"?>" data-toggle="buttons">
                            <?php echo $field->render(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row row-margin row-button">
    <?php if($tournee->appellation != 'VTSGN'): ?>
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div id="recap_cepages" class="btn-group">
            <?php foreach($tournee->getProduits() as $produit): ?>
            <button class="btn btn-default btn-default-step btn-sm disabled" data-cepage="<?php echo $produit->getHashForKey() ?>"><?php echo $produit->getLibelleLong() ?> <span class="badge" style="color: white">0</span></button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-3">
        <a href="<?php echo url_for('degustation_creation', $tournee) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-center lead text-muted">
        <span data-dynamic-value="nb-lots"><?php echo $tournee->getNbLots() ?></span> lot(s) pour <span data-dynamic-value="nb-operateurs"><?php echo count($tournee->getOperateursPrelevement()) ?></span> opérateur(s)
    </div>
    <div class="col-xs-3 text-right">
        <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer</button>
    </div>
</div>

</form>
