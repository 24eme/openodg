<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>

<div class="page-header no-border">
    <h2><?php echo $tournee->appellation_libelle; ?>&nbsp;<span class="small"><?php echo getDatesPrelevements($tournee); ?></span>&nbsp;<div class="btn btn-default btn-sm"><?php echo count($tournee->operateurs) ?>&nbsp;Opérateurs</div></h2>
</div>

<form action="<?php echo url_for('degustation_courriers', $tournee); ?>" method="post" class="form-horizontal">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
<?php include_partial('degustation/notes', array('tournee' => $tournee, 'form' => $form)); ?>
<div class="row row-margin">
    <div class="col-xs-6 text-left">
            <a class="btn btn-primary btn-lg btn-upper" href="<?php echo url_for('degustation_visualisation', $tournee) ?>"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>             
    <div class="col-xs-6 text-right">
        <button type="submit" class="pull-right btn btn-default btn-lg btn-upper">Valider</button>
    </div>
</div>
</form>
