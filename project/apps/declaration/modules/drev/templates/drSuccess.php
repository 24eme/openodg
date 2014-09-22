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
    <div class="col-xs-6"><a href="<?php echo url_for("home") ?>#drev" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a></div>
    <div class="col-xs-6 text-right">
        <a class="btn btn-primary btn-lg btn-upper" href="<?php echo url_for("drev_exploitation", $drev) ?>">Passer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></a>
    </div>
</div>
