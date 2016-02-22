<?php include_partial('tirage/step', array('step' => 'confirmation', 'tirage' => $tirage)) ?>

<br/>
<div class="page-header">
    <h2>Confirmation de validation de votre déclaration de tirage</h2>
</div>
<div class="row">
    <div class="col-xs-12">
        <p>Votre déclaration de tirage a bien été enregistrée par l'AVA.</p>
        <p>Vous recevrez le PDF de votre déclaration par email dès que l'AVA aura pu valider les documents que vous vous êtes engagés à lui faire parvenir.</p>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("home") ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour à votre espace</a>
    </div>
</div>