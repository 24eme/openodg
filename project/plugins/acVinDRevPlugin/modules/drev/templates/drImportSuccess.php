<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => null, 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération de la Déclaration de Récolte <small>sur le plateforme du CIVA</small></h2>
</div>

<p class="text-danger">La Déclaration de Récolte n'a pas pu être récupérée.</p>

<p>Si vous n'avez pas encore déclaré votre Déclaration de Récolte sur le portail du CIVA, il est conseillé de le faire avant de déclarer votre Déclaration de Revendication.</p>

<p>Si vous avez déjà télédéclaré votre déclaration de récolte et que la récupération de vos données ne s'est pas faite correctement, veuillez contacter l'ODG-AVA</p>

<div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("drev_delete", $drev) ?>" class="btn btn-default btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Annuler et revenir plus tard</a></div>
    <div class="col-xs-6 text-right">
        <a class="btn btn-link" href="<?php echo url_for("drev_revendication", $drev) ?>"><small>Continuer sans la Déclaration de Récolte</a><span class="eleganticon arrow_carrot-right"></span>
    </div>
</div>
