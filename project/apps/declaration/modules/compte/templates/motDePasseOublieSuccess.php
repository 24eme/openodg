<?php $url = sprintf("%s?%s", sfConfig::get('app_url_compte_mot_de_passe_oublie'),http_build_query(array('service' => url_for("accueil", array(), true)))); ?>
<div class="page-header">
    <h2>Mot de passe oublié</h2>
</div>
<div class="row">
    <div class="col-xs-12">
        <p>La demande de mot de passe oublié s'effectue sur l'espace professionnel du CIVA.<br /><br />
        En continuant, vous allez être redirigé automatiquement <a class="btn-link" href="<?php echo $url ?>">vers l'espace professionnel du CIVA</a>.<br /><br />
        Une fois votre mot de passe modifié, vous pourrez directement vous connecter sur le portail de l'AVA.</p>
    </div>
</div>
<div class="row row-margin row-button">
    <div class="col-xs-12 text-right">
        <a class="btn btn-default btn-lg btn-upper" href="<?php echo $url ?>">
            Continuer la demande de mot de passe oublié
            <span class="eleganticon arrow_carrot-right"></span>
        </a>
    </div>
</div>
