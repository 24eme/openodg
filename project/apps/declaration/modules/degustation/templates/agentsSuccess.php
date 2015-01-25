<?php use_javascript("degustation.js", "last") ?>

<?php include_partial('degustation/step', array('active' => 'agents')); ?>

<div class="page-header">
    <h2>Choix des agents de prélevements</h2>
</div>

<form id="form_degustation_choix_operateurs" action="" methode="post" class="form-horizontal">

<div class="row">
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div class="btn-group">
            <a id="nav_tous" class="btn btn-info active" href="">Tous <span class="badge">25</span></a>
            <a  id="nav_a_prelever" class="btn btn-default"  href="">Séléctionné <span class="badge">0</span></a>
        </div>
    </div>
    <div class="col-xs-12">
        <div id="listes_operateurs" class="list-group">
            <?php for($i = 0; $i <= 24; $i++): ?>
            <div class="list-group-item col-xs-12 clickable">
                <div class="col-xs-3">M. NOM PRENOM <?php echo $i ?></div>
                <div class="col-xs-8">
                    <select multiple="multiple" data-placeholder="Sélectionner des dates" class="form-control select2 select2-offscreen select2autocomplete hidden">
                        <option></option>
                        <option>Lundi 2 janvier 2015</option>
                        <option>Mardi 3 janvier 2015</option>
                        <option>Mercredi 4 janvier 2015</option>
                        <option>Jeudi 5 janvier 2015</option>
                        <option>Vendredi 6 janvier 2015</option>
                        <option>Samedi 7 janvier 2015</option>
                        <option>Dimanche 8 janvier 2015</option>
                        <option>Lundi 9 janvier 2015</option>
                        <option>Mardi 10 janvier 2015</option>
                        <option>Mercredi 11 janvier 2015</option>
                        <option>Jeudi 12 janvier 2015</option>
                        <option>Vendredi 13 janvier 2015</option>
                    </select>
                </div>
                <div class="col-xs-1">
                    <button class="btn btn-success btn-sm pull-right" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                    <button class="btn btn-danger btn-sm pull-right hidden" style="opacity: 0.7;" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_degustation') ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <a href="<?php echo url_for('degustation_prelevements') ?>" class="btn btn-default btn-lg btn-upper">Continuer</a>
    </div>
</div>

</form>