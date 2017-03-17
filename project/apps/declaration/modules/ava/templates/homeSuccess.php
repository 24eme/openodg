<?php use_javascript("main.js", "last") ?>

<div class="page-header">
    <h2><?php if ($etablissement->needEmailConfirmation() && !$sf_user->isAdmin()): ?>Confirmation de votre e-mail<?php else: ?>Eléments déclaratifs<?php endif; ?></h2>
</div>

<?php if ($sf_user->isAdmin()): ?>
    <div class="row row-margin">
        <form method="post" action="" role="form" class="form-horizontal">
            <?php echo $formLogin->renderHiddenFields(); ?>
            <?php echo $formLogin->renderGlobalErrors(); ?>
            <div class="form-group">
                <?php echo $formLogin["login"]->renderError(); ?>
                <div class="col-xs-8 col-xs-offset-1">
                    <?php
                    echo $formLogin["login"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocompleteremote select2SubmitOnChange",
                        "placeholder" => $etablissement->nom.' ('.$etablissement->cvi.') à '.$etablissement->commune.' ('.$etablissement->code_postal.')',
                        "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                    ));
                    ?>
                </div>
                <div class="col-xs-2">
                    <button class="btn btn-default btn-lg" type="submit">Valider</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>
<?php if ($etablissement->needEmailConfirmation() && !$sf_user->isAdmin()): ?>
    <form action="<?php echo url_for("home") ?>" method="post" class="form-horizontal">
        <p>Pour votre première connexion sur le portail de l'Association des Viticulteurs d'Alsace, vous devez confirmer votre adresse e-mail.</p>
        <?php include_partial('etablissement/formConfirmationEmail', array('form' => $form, 'etablissement' => $etablissement)); ?>
        <div class="row">
            <div class="col-xs-12 text-right">
                <button type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider</button>
            </div>
        </div>
    </form>
<?php else: ?>
    <h4>Veuillez trouver ci-dessous l'ensemble de vos éléments déclaratifs</h4>
    <div class="row">
        <?php include_component('drev', 'monEspace'); ?>
        <?php include_component('drevmarc', 'monEspace'); ?>
        <?php include_component('parcellaire', 'monEspace'); ?>
        <?php include_component('parcellaireCremant', 'monEspace'); ?>
        <?php include_component('tirage', 'monEspace'); ?>
    </div>
    <?php include_component('ava', 'history'); ?>
<?php endif; ?>
