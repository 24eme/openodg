<?php use_javascript("degustation.js?201504020331", "last") ?>
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
    <!--<div class="col-xs-12" style="padding-bottom: 15px;">
        <div id="recap_cepages" class="btn-group">
            <?php foreach($tournee->getProduits() as $produit): ?>
            <button class="btn btn-default btn-default-step btn-sm disabled" data-cepage="<?php echo $produit->getHashForKey() ?>"><?php echo $produit->getLibelleLong() ?> <span class="badge" style="color: white">0</span></button>
            <?php endforeach; ?>
        </div>
    </div>-->
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div class="btn-group">
            <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge"><?php echo count($tournee->operateurs) ?></span></a>
            <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">À prélever <span class="badge"><?php echo count($tournee->getOperateursPrelevement()) ?></span></a>
        </div>
    </div>
    <div class="col-xs-12">
        <div id="listes_operateurs" class="list-group">
            <?php foreach($form as $key => $field): ?>
                <?php if($field->isHidden()): continue; endif; ?>
                <?php $operateur = $tournee->operateurs->get($key); ?>
                <?php $exist = count($operateur->getLotsPrelevement()) > 0; ?>
                <div <?php if($exist): ?>data-state="active"<?php endif; ?> class="list-group-item list-group-item-item col-xs-12 <?php if(!$exist): ?>clickable<?php else: ?>list-group-item-success<?php endif; ?>">
                <div class="col-xs-5"><?php echo $operateur->raison_sociale ?> <small>(<?php echo $operateur->cvi ?>)</small> <small class="text-muted"><br /><?php echo $operateur->commune ?></small></div>
                <div class="col-xs-3 text-left"><small class="text-muted">Pour le </small><?php echo format_date($operateur->date_demande, "D", "fr_FR") ?><!--<small class="text-muted">Prélevé le</small> 2012, 2014--><?php if($operateur->reporte): ?><br /><span class="label label-warning">Reporté</span><?php endif; ?></div>
                <div class="col-xs-3">
                    <?php $attrs = array("class" => "form-control input-sm", "data-selection-mode" => "all", "data-placeholder" => "Sélectionné un lot") ?>
                    <?php if(!$exist): ?>
                        <?php $attrs["class"] .= " hidden"; ?>
                        <?php $attrs["disabled"] = "disabled"; ?>
                    <?php endif; ?>
                    <?php echo $field->render($attrs); ?>
                </div>
                <div class="col-xs-1">
                    <button class="btn btn-success btn-sm pull-right <?php if($exist): ?>hidden<?php endif; ?>" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                    <button class="btn btn-danger btn-sm pull-right <?php if(!$exist): ?>hidden<?php endif; ?>" style="opacity: 0.7;" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_creation', $tournee) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer</button>
    </div>
</div>

</form>
