<?php use_javascript("degustation.js", "last") ?>

<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => 'degustateurs')); ?>

<div class="page-header">
    <h2>Choix des dégustateurs</h2>
</div>

<ul class="nav nav-tabs">
    <?php foreach($types as $type_key => $libelle): ?>
        <li role="presentation" class="<?php if($type == $type_key): ?>active<?php endif; ?>"><a href="<?php echo url_for('degustation_degustateurs_type', array('sf_subject' => $degustation, 'type' => $type_key)) ?>"><?php echo $libelle ?></a></li>
    <?php endforeach; ?>
</ul>

<form action="" method="post" class="form-horizontal">
    <div class="row">
        <div class="col-xs-12" style="padding-bottom: 15px;">
            <div class="btn-group">
                <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge"><?php echo count($degustateurs) ?></span></a>
                <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">Séléctionné <span class="badge">0</span></a>
            </div>
        </div>
        <div class="col-xs-12">
            <div id="listes_operateurs" class="list-group">
                <?php foreach($degustateurs as $degustateur): ?>
                <div class="list-group-item list-group-item-item col-xs-12 clickable">
                    <div class="col-xs-5"><?php echo $degustateur->nom_a_afficher ?></div>
                    <div class="col-xs-3 text-left"><!--<small class="text-muted">Venu en</small> 2012, 2014--></div>
                    <div class="col-xs-3">
                        <!--<small class="text-muted">Formé en</small> 2013, 2014-->
                    </div>
                    <div class="col-xs-1">
                        <button class="btn btn-success btn-sm pull-right" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                        <button class="btn btn-danger btn-sm pull-right hidden" style="opacity: 0.7;" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <?php if($type_previous): ?>
             <a href="<?php echo url_for('degustation_degustateurs_type', array('sf_subject' => $degustation, 'type' => $type_previous)) ?>" class="btn btn-primary btn-primary-step btn-lg btn-upper">Précédent</a>
            <?php else: ?>
            <a href="<?php echo url_for('degustation_operateurs', $degustation) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
            <?php endif; ?>
        </div>
        <div class="col-xs-6 text-right">
            <?php if($type_next): ?>
                <a href="<?php echo url_for('degustation_degustateurs_type', array('sf_subject' => $degustation, 'type' => $type_next)) ?>" class="btn btn-default btn-default-step btn-lg btn-upper">Continuer</a>
            <?php else: ?>
            <a href="<?php echo url_for('degustation_agents', $degustation) ?>" class="btn btn-default btn-lg btn-upper">Continuer</a>
            <?php endif; ?>
        </div>
    </div>
</form>
