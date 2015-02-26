<?php use_javascript("degustation.js", "last") ?>
<?php use_helper("Date") ?>

<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_OPERATEURS)); ?>

<div class="page-header">
    <h2>Choix des Opérateurs</h2>
</div>

<form id="form_degustation_choix_operateurs" action="<?php echo url_for('degustation_operateurs', $degustation) ?>" method="post" class="form-horizontal">

<input type="hidden" id="nb_a_prelever" value="<?php echo $nb_a_prelever ?>"/>

<div class="row">
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div id="recap_cepages" class="btn-group">
            <?php foreach($degustation->getProduits() as $produit): ?>
            <button class="btn btn-default btn-default-step disabled btn-sm" data-cepage="<?php echo $produit->getHashForKey() ?>" style="opacity: 1;"><?php echo $produit->getLibelleLong() ?> <span class="badge" style="color: white">0</span></button>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div class="btn-group">
            <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge"><?php echo count($prelevements) ?></span></a>
            <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">À preléver <span class="badge"><?php echo count($degustation->prelevements) ?></span></a>
        </div>
    </div>
    <div class="col-xs-12">
        <div id="listes_operateurs" class="list-group">
            <?php foreach($prelevements as $id => $prelevement): ?>
            <?php $exist = $degustation->prelevements->exist($prelevement->identifiant); ?>
            <div <?php if($exist): ?>data-state="active"<?php endif; ?> class="list-group-item list-group-item-item col-xs-12 <?php if(!$exist): ?>clickable<?php else: ?>list-group-item-success<?php endif; ?>">
                <div class="col-xs-4"><?php echo $prelevement->raison_sociale ?> <small class="text-muted">à <?php echo $prelevement->commune ?></small></div>
                <div class="col-xs-3 text-left"><small class="text-muted">Pour le </small><?php echo format_date($prelevement->date, "D", "fr_FR") ?><!--<small class="text-muted">Prélevé le</small> 2012, 2014--></div>
                <div class="col-xs-4">
                    <select <?php if(!$exist): ?>disabled="disabled"<?php endif; ?> name="operateurs[<?php echo $id ?>]" data-auto="true" data-placeholder="Sélectionner" class="form-control input-sm <?php if(!$exist): ?>hidden<?php endif; ?>">
                        <?php foreach($prelevement->lots as $lot_key => $lot): ?>
                        <option <?php if($exist && $degustation->prelevements->get($prelevement->identifiant)->lots->exist($lot_key)): ?>selected="selected"<?php endif; ?> value="<?php echo $lot_key ?>"><?php echo $lot->libelle ?> - <?php echo $lot->nb ?> lot(s)</option>
                        <?php endforeach; ?>
                    </select>
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
        <a href="<?php echo url_for('degustation_creation', $degustation) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer</button>
    </div>
</div>

</form>
