<div class="page-header">
    <h2>Création d'un nouveau compte <?php echo CompteClient::getInstance()->getCompteTypeLibelle($type_compte); ?></h2>
</div>

<form action="<?php echo url_for("compte_creation_admin", array('type_compte' => $type_compte)) ?>" method="post" class="form-horizontal">

    <div class="row">
        <?php include_partial('modificationAdminForm', array('form' => $form)); ?>
    </div>
    
<div class="row row-margin row-button">
    <div class="col-xs-4"><a href="<?php echo url_for("compte_recherche") ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>Annuler</a></div>
    <div class="col-xs-4 text-center">
        <a href="" id="btn_exploitation_annuler" class="btn btn-danger hidden">Annuler</a>
        <button id="btn_compte_creation" type="submit" class="btn btn-lg btn-default">Valider</button>
    </div>
</div>
</form>