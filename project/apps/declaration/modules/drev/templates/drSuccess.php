<?php include_partial('drev/step', array('step' => null, 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération de la DR <small>sur le plateforme du CIVA</small></h2>
</div>

<p>Voulez vous récupérer la DR ?</p>
<div class="row">
<div class="col-xs-12 text-center">
         <a href="<?php echo url_for("drev_dr_recuperation", $drev) ?>" class="btn btn-default btn-lg">Récupérer les données DR</a>
    </div>
</div>
<div class="row row-margin">
    <div class="col-xs-6"><a href="<?php echo url_for("home") ?>#drev" class="btn btn-primary btn-lg"><span class="eleganticon arrow_carrot-left pull-left"></span>Retour</a></div>
    <div class="col-xs-6 text-right">
        <a class="btn btn-primary" href="<?php echo url_for("drev_exploitation", $drev) ?>"><span class="eleganticon arrow_carrot-right pull-right"></span>Passer</a>
       
    </div>
</div>
