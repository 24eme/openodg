<?php include_partial('drev/step', array('step' => null, 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération de la DR <small>sur le plateforme du CIVA</small></h2>
</div>

<p class="text-danger text-center">Oops, La déclaration de récolte n'a pas pu être récupéré !</p>

<div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("home") ?>#drev" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a></div>
    <div class="col-xs-6 text-right">
        <a class="btn btn-default btn-lg btn-upper" href="<?php echo url_for("drev_exploitation", $drev) ?>">Continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></a>
    </div>
</div>

