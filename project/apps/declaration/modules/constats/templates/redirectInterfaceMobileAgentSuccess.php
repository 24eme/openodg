<?php use_helper("Date"); ?>

<div class="row row-margin text-left" style="margin-top: 10px;">
    <div class="col-xs-6">
    <?php if(!$sf_user->hasCredential(myUser::CREDENTIAL_TOURNEE)): ?>
    <a href="<?php echo url_for('constats',array('jour' => date('Y-m-d'))); ?>" class="btn btn-default btn-default-step"><span style="font-size: 20px" class="eleganticon arrow_carrot-left"></span>&nbsp;Retour à l'application</a>
    <?php endif; ?>
    </div>
    <div class="col-xs-6 text-right">
    <a href="<?php echo url_for('auth_logout'); ?>" class="btn btn-link ">Se déconnecter</a>
    </div>
</div>

<div class="text-center page-header">
    <h2>Tournées</h2>
    <h2><?php echo ucfirst(format_date(date('Y-m-d'), "P", "fr_FR")); ?></h2>
</div>

<div class="row row-margin">
<form id="form_ajout_agent_tournee" action="<?php echo url_for('tournee_agent_accueil'); ?>" method="post" class="form-horizontal" name="<?php echo $form->getName(); ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="col-xs-12">
        <div class="form-group <?php if ($form["agent"]->hasError()): ?>has-error<?php endif; ?>">
            <div class="col-xs-12">
            <?php echo $form["agent"]->renderError(); ?>
            <?php echo $form["agent"]->renderLabel(); ?> :
            <?php echo $form["agent"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocomplete", "placeholder" => "Votre nom")); ?>
            </div>
        </div>
    </div>

    <div class="col-xs-12">
        <button type="submit" class="btn btn-block btn-default btn-lg btn-upper">Valider</button>
    </div>
</form>
</div>