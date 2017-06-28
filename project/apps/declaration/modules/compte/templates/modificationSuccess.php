<ol class="breadcrumb">
    <li><a href="<?php echo url_for('compte_recherche'); ?>">Contacts</a></li>
    <li><a href="<?php echo url_for('compte_visualisation', $compte); ?>"><?php echo $compte->getNomAAfficher() ?> (<?php echo $compte->getIdentifiantAAfficher() ?>)</a></li>
    <li class="active"><a href="">Modification</a></li>
</ol>

<div class="page-header">
    <h2>Modification du compte <?php echo $compte->identifiant; ?> (<?php echo CompteClient::getInstance()->getCompteTypeLibelle($compte->getTypeCompte()); ?>)</h2>
</div>

<form action="<?php echo url_for("compte_modification", $compte) ?>" method="post" class="form-horizontal">

    <div class="row">
        <?php include_partial('modificationForm', array('form' => $form)); ?>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("compte_visualisation", $compte) ?>" class="btn btn-primary btn-lg btn-upper">
                <span class="eleganticon arrow_carrot-left"></span>Annuler
            </a>
        </div>
        <div class="col-xs-4 text-center">
            <button type="submit" class="btn btn-lg btn-default">Valider</button>
        </div>
    </div>
</form>
