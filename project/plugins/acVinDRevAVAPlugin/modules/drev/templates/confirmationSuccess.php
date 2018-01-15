<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>

<div class="page-header">
    <h2>Confirmation de validation de votre déclaration</h2>
</div>
<div class="row">
    <div class="col-xs-12">
        <p>
            Merci d'avoir validé votre déclaration. <br />
            <br />
            Vous allez recevoir un mail de confirmation.
        </p>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("declaration_etablissement", $drev->getEtablissementObject()) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
</div>
