<?php use_javascript("degustation.js", "last") ?>

<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_DEGUSTATEURS)); ?>

<div class="page-header">
    <h2>Choix des dégustateurs</h2>
</div>

<ul class="nav nav-tabs">
    <?php foreach($types as $type_key => $libelle): ?>
        <li role="presentation" class="<?php if($type == $type_key): ?>active<?php endif; ?>"><a href="<?php echo url_for('degustation_degustateurs_type', array('sf_subject' => $degustation, 'type' => $type_key)) ?>"><?php echo $libelle ?> <?php if($type != $type_key): ?><span class="badge"><?php echo ($degustation->degustateurs->exist($type_key)) ? count($degustation->degustateurs->get($type_key)) : 0 ?></span><?php endif; ?></a></li>
    <?php endforeach; ?>
</ul>

<form action="" method="post" class="form-horizontal">
    <div class="row">
        <div class="col-xs-12" style="padding-bottom: 15px;">
            <div class="btn-group">
                <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge"><?php echo count($degustateurs) ?></span></a>
                <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">Séléctionné <span class="badge"><?php echo count($noeud) ?></span></a>
            </div>
        </div>
        <div class="col-xs-12">
            <div id="listes_operateurs" class="list-group">
                <?php foreach($degustateurs as $degustateur): ?>
                <?php $exist = $noeud->exist($degustateur->_id); ?>
                <div <?php if($exist): ?>data-state="active"<?php endif; ?> class="list-group-item list-group-item-item col-xs-12 <?php if(!$exist): ?>clickable<?php else: ?>list-group-item-success<?php endif; ?>">
                    <input <?php if(!$exist): ?>disabled="disabled"<?php endif; ?> type="hidden" name="degustateurs[<?php echo $degustateur->_id ?>]" value="1" />
                    <div class="col-xs-5"><?php echo $degustateur->nom_a_afficher ?></div>
                    <div class="col-xs-3 text-left"><!--<small class="text-muted">Venu en</small> 2012, 2014--></div>
                    <div class="col-xs-3">
                        <!--<small class="text-muted">Formé en</small> 2013, 2014-->
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
             <a href="<?php echo url_for('degustation_degustateurs_type_precedent', array('sf_subject' => $degustation, 'type' => $type)) ?>" class="btn btn-primary btn-primary-step btn-lg btn-upper">Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer</button>
        </div>
    </div>
</form>
