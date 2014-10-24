<div class="page-header">
    <h2>Création de compte</h2>
</div>
<div class="row">
    <div class="col-xs-12">
        <p>La création de compte s'effectue sur l'espace professionel du CIVA.<br /><br />
        Une fois créé, ce compte vous permettra de vous connecter sur le portail de l'AVA.</p>
    </div>
</div>
<div class="row row-margin row-button">
    <div class="col-xs-12 text-right">
        <a class="btn btn-default btn-lg btn-upper" href="<?php echo sprintf("%s?%s", sfConfig::get('app_url_compte_creation'),http_build_query(array('service' => url_for("compte_creation_confirmation", array(), true)))) ?>">
            Créez mon compte
            <span class="eleganticon arrow_carrot-right"></span>
        </a>
    </div>
</div>