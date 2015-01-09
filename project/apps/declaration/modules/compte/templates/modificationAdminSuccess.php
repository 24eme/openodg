<div class="page-header">
    <h2>Modification du compte <?php echo $compte->identifiant; ?> (<?php echo CompteClient::getInstance()->getCompteTypeLibelle($compte->getTypeCompte()); ?>)</h2>
</div>

<form action="<?php echo url_for("compte_modification_admin", array('id' => $compte->identifiant)) ?>" method="post" class="form-horizontal">

    <div class="row">
        <?php include_partial('modificationAdminForm', array('form' => $form)); ?>   
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("home") ?>" class="btn btn-primary btn-lg btn-upper">
                <span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à mon espace</small>
            </a>
        </div>
        <div class="col-xs-4 text-center">
            <button type="submit" class="btn btn-warning">Valider</button>
        </div>
    </div>
</form>