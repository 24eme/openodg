<?php use_javascript("main.js", "last") ?>

<div class="page-header">
    <h2><?php if ($etablissement->needEmailConfirmation() && !$sf_user->isAdmin()): ?>Confirmation de votre e-mail<?php else: ?>Eléments declaratifs<?php endif; ?></h2>
</div>
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


