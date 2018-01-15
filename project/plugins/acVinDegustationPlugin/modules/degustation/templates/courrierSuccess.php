<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_visualisation', $tournee); ?>">Tournée <?php echo $tournee->getLibelle(); ?>  le <?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></a></li>
  <li class="active"><a href="">Courriers</a></li>
</ol>

<div class="page-header no-border">
    <h2><?php echo $tournee->libelle; ?>&nbsp;<span class="small"><?php echo getDatesPrelevements($tournee); ?></span>&nbsp;<div class="btn btn-default btn-sm"><?php echo count($tournee->operateurs) ?>&nbsp;Opérateurs</div></h2>
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
