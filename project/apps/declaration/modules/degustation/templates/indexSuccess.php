<?php include_partial('admin/menu', array('active' => 'tournees')); ?>

<form action="" method="post" class="form-horizontal">
    
    <div class="row">
        <div class="col-xs-8">
            <div class="form-group">
                <label for="inputDate1" class="col-xs-6 control-label">Date de dégustation</label>
                <div class="col-xs-6">
                    <div class="input-group date-picker-all-days">
                        <input type="date" class="form-control" id="inputDate1" placeholder="">
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-xs-6 control-label">Appellation / Mention</label>
                <div class="col-xs-6">
                    <select class="form-control">
                        <option selected="selected"></option>
                        <option>AOC Alsace</option>
                        <option>VT / SGN</option>
                        <option>Grands Crus</option>
                    </select>
                </div>
            </div>
            <div class="form-group text-right">
            <a href="<?php echo url_for('degustation_creation') ?>" class="btn btn-default btn-lg btn-upper">Créer</a>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 20px;">
    <di class="col-xs-12">
            <div class="list-group">
                <a href="<?php echo url_for('degustation_prelevements') ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-default">Saisie</span>
                    </span>
                </a>
                <a href="<?php echo url_for('degustation_tournee') ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-info">Tournée</span>
                    </span>
                </a>
                <a href="<?php echo url_for('degustation_affectation') ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-warning">Affectation des vins</span>
                    </span>
                </a>
                <a href="" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-danger">Dégustation</span>
                    </span>
                </a>
                <a href="" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-success">Terminé</span>
                    </span>
                </a>
            </div>
</div>
</form>
