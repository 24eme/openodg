<?php use_javascript('tournee.js'); ?>

<section id="commissions">
    <div class="page-header">
        <h2>Dégustation du 23/02/2014</h2>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="list-group">
                <?php $nb_validee = 1; ?>
                <?php for($i=1; $i <= 3; $i++): ?>
                <a href="#degustateurs_<?php echo $i ?>" class="list-group-item <?php if($nb_validee >= $i): ?>list-group-item-success<?php endif; ?> col-xs-12 link-to-section">
                    <div class="col-xs-1">
                        <strong style="font-size: 32px;"><?php echo $i ?></strong>
                    </div>
                    <div class="col-xs-10">
                    <strong class="lead">Commission</strong><br />
                    12 dégustateurs et 10 vins à déguster
                    </div>
                    
                    <div class="col-xs-1">
                        <span class="<?php if($nb_validee >= $i): ?>glyphicon glyphicon-check<?php else: ?>glyphicon glyphicon-unchecked<?php endif; ?>" style="font-size: 40px; margin-top: 5px;"></span>
                    </div>
                </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation') ?>" class="btn btn-default btn-default-step btn-lg btn-upper btn-block">Retour</a>
        </div>
        <div class="col-xs-6">
            <a href="#commissions" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Transmettre</a>
        </div>
    </div>
</section>
<?php for($i=1; $i <= 3; $i++): ?>
<section id="degustateurs_<?php echo $i ?>" class="hidden">
    <div class="page-header">
        <h2>Dégustateurs présents<br /><small>Commission <?php echo $i ?></small></h2>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="list-group">
                <?php for($j=1; $j <= 20; $j++): ?>
                <?php $checked = (rand(1,3) == 3) ?>
                <a href="#" class="list-group-item <?php if($checked): ?>list-group-item-success<?php endif; ?> col-xs-12">
                    <div class="col-xs-11">
                        <span class="lead">Dégustateur <?php echo $j ?></span>
                    </div>
                    <div class="col-xs-1 text-right">
                        <span style="font-size: 26px;" class="glyphicon glyphicon-check glyphicon <?php if($checked): ?>glyphicon-check<?php else: ?>glyphicon-unchecked<?php endif; ?>"></span>
                    </div>
                </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="#commissions" class="btn btn-default btn-default-step btn-lg col-xs-6 btn-block btn-upper link-to-section">Retour</a>
        </div>
        <div class="col-xs-6">
            <a href="#vins_<?php echo $i ?>" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Démarrer</a>
        </div>
    </div>
</section>

<section id="vins_<?php echo $i ?>" class="hidden">
    <div class="page-header">
        <h2>Vins à déguster<br /><small>Commission <?php echo $i ?></small></h2>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="list-group">
                <?php $nb_validee = 2; ?>
                <?php for($j=1; $j <= 12; $j++): ?>
                <?php $checked = ($j <= $nb_validee) ?>
                <a href="#vin_<?php echo $i ?>_<?php echo $j ?>" class="list-group-item <?php if($checked): ?>list-group-item-success<?php endif; ?> col-xs-12 link-to-section">
                    <div class="col-xs-1">
                        <strong style="font-size: 32px;"><?php echo $j ?></strong>
                    </div>
                    <div class="col-xs-5">
                        <span class="lead">Cépage <?php echo $j ?></span>
                    </div>
                    <div class="col-xs-5 text-right">
                        <?php if($checked): ?>
                            <span>Qualité technique : <span><?php echo rand(0,5) ?></span></span><br />
                            <span>Matière : <span><?php echo rand(0,5) ?></span></span><br />
                            <span>Typicité : <span><?php echo rand(0,5) ?></span></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-xs-1">
                        <span class="<?php if($checked >= $i): ?>glyphicon glyphicon-check<?php else: ?>glyphicon glyphicon-unchecked<?php endif; ?>" style="font-size: 40px; margin-top: 5px;"></span>
                    </div>
                </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="#degustateurs_<?php echo $i ?>" class="btn btn-default btn-default-step btn-lg col-xs-6 btn-block btn-upper link-to-section">Retour</a>
        </div>
        <div class="col-xs-6">
            <a href="#commissions" class="btn btn-warning btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
        </div>
    </div>
</section>

<?php for($j=1; $j <= 12; $j++): ?>
<section id="vin_<?php echo $i ?>_<?php echo $j ?>" class="hidden">
    <div class="page-header">
        <h2>Lot n° <?php echo $j ?> de Cépage<br /><small>Commission <?php echo $i ?></small></h2>
    </div>
    <div class="row">
        <div class="col-xs-12">
           <form class="form-horizontal">
                <?php $controles = array("Qualité technique", "Matière", "Typicité", "Concentration", "Équilibre"); ?>
                <?php foreach($controles as $controle): ?>
                <div class="form-group form-group-lg">
                    <div class="col-xs-12">
                        <label class="col-xs-3 control-label lead"><?php echo $controle ?></label>
                        <div class="col-xs-2">
                            <select class="form-control input-lg">
                                <option>Note</option>
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option>4</option>
                                <option>5</option>
                            </select>
                        </div>
                        <div class="col-xs-7">
                           <select multiple="multiple" data-placeholder="Séléction de défaut(s)" class="form-control input-lg select2 select2-offscreen select2autocomplete">
                            <option></option>
                            <option>Bouchonné</option>
                            <option>Vert</option>
                            <option>Liquoreux</option>
                        </select>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="form-group form-group-lg" style="padding-top: 20px;">
                    <label class="col-xs-3 control-label lead text-muted">Appréciation</label>
                    <div class="col-xs-9">
                        <div class="col-xs-12">
                            <textarea placeholder="Saisissez vos appréciations" class="form-control input-lg"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="#vins_<?php echo $i ?>" class="btn btn-default btn-default-step btn-lg col-xs-6 btn-block btn-upper link-to-section">Retour</a>
        </div>
        <div class="col-xs-6">
            <a href="#vins_<?php echo $i ?>" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Valider</a>
        </div>
    </div>
</section>
<?php endfor; ?>
<?php endfor; ?>