<?php use_helper("Date") ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('constats',array('jour' => date('Y-m-d'))); ?>">Constats VT-SGN</a></li>
  <li><a href="<?php echo url_for('constats_planification_jour', array('jour' => $jour)); ?>">Tournées du <?php echo ucfirst(format_date($jour, "P", "fr_FR")); ?></a></li>
  <li class="active"><a href="">Ajouter un agent</a></li>
</ol>

<div class="page-header">
    <h2>Ajouter un Agent </h2>
</div>

<form id="form_ajout_agent_tournee" action="<?php echo url_for('constats_planification_ajout_agent', array('jour' => $jour,'retour' => $retour)); ?>" method="post" class="form-horizontal" name="<?php echo $form->getName(); ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>


    <div class="row">
        <div class="col-xs-10 col-xs-offset-1">
            <div class="form-group <?php if ($form["date"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form['date']->renderError(); ?>
                <div class="input-group date-picker-week" >
                    <?php echo $form['date']->render(array('class' => 'form-control')); ?>
                    <div class="input-group-addon">
                        <span class="glyphicon-calendar glyphicon"></span>
                    </div>
                </div>
            </div>
            <div class="form-group <?php if ($form["agent"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["agent"]->renderError(); ?>

                    <?php echo $form["agent"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "placeholder" => "Séléctionnez un agent")); ?>
            </div>
        </div>
    </div>

    <div class="row row-margin">
        <div class="col-xs-10 col-xs-offset-1 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Ajouter</button>
        </div>
    </div>

</form>
