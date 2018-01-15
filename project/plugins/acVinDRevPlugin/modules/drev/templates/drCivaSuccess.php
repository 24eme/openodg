<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>

<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération des données de la Déclaration de Récolte <small>sur la plateforme du CIVA</small></h2>
</div>

<p>Vous allez être redirigé sur la plateforme du CIVA afin de récupérer les données de votre Déclaration de Récolte.</p>
<div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a></div>
    <div class="col-xs-6 text-right">
        <a class="btn btn-default btn-lg btn-upper" href="<?php echo url_for("drev_dr_recuperation", $drev) ?>">Récupérer les données&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></a>
    </div>
</div>
