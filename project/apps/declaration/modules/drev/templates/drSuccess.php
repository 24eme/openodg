<?php include_partial('drev/step', array('step' => null, 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération de la DR <small>sur le plateforme du CIVA</small></h2>
</div>

<p>Vous allez être rediriger sur la plateforme du CIVA afin de récupérer les données de la déclaration de Récolte.</p>
<div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("home") ?>#drev" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a></div>
    <div class="col-xs-6 text-right">
        <a class="btn btn-link" href="<?php echo url_for("drev_exploitation", $drev) ?>">Passer</a>
        <a class="btn btn-default btn-lg btn-upper" href="<?php echo url_for("drev_dr_recuperation", $drev) ?>">Récupérer les données de la DR&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></a>
    </div>
</div>

