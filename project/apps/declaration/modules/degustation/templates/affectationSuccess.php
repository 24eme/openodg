<?php use_javascript('tournee.js'); ?>

<section id="commissions">
    <div class="page-header">
        <h2>Affectation des vins <small>Dégustation du 23/02/2014</small></h2>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="list-group">
                <?php $nb_validee = 1; ?>
                <?php for($i=1; $i <= 3; $i++): ?>
                <a href="#commission_<?php echo $i ?>" class="list-group-item <?php if($nb_validee >= $i): ?>list-group-item-success<?php endif; ?> col-xs-12 link-to-section">
                    <div class="col-xs-1">
                        <strong style="font-size: 32px;"><?php echo $i ?></strong>
                    </div>
                    <div class="col-xs-10">
                    <strong class="lead">Commission</strong><br />
                    12 vins
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
<section id="commission_<?php echo $i ?>" class="hidden">
    <div class="page-header">
        <h2>Commission <?php echo $i ?></h2>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <form class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12">
                        <select id="commission_<?php echo $i ?>_select" data-placeholder="Séléctionnez un numéro de prélévement" class="form-control input-lg select2 select2-offscreen select2autocomplete">
                            <option></option>
                            <option>RI02AB</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="#commissions" class="btn btn-warning btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
        </div>
        <div class="col-xs-6">
            <a href="#prelevement_<?php echo $i ?>_<?php echo rand(1,10) ?>" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Ajouter</a>
        </div>
    </div>
</section>
<?php for($j=1; $j <= 10; $j++): ?>
<section id="prelevement_<?php echo $i ?>_<?php echo $j ?>" class="hidden">
    <div class="page-header">
        <h2>N° RI02AB Riesling <small>Commission <?php echo $i ?></small></h2>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <p class="text-center"><span  style="font-size: 36px;" class="text-muted">N° </span><strong style="font-size: 40px;" ><?php echo $j ?></strong></p>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="#commission_<?php echo $i ?>" class="btn btn-danger btn-lg col-xs-6 btn-block btn-upper link-to-section">Annuler</a>
        </div>
        <div class="col-xs-6">
            <a href="#commission_<?php echo $i ?>" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Confirmer</a>
        </div>
    </div>
</section>
<?php endfor; ?>
<?php endfor; ?>