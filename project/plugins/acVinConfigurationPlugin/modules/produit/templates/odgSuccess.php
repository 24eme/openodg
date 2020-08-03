<ol class="breadcrumb">
    <li><a href="<?php echo url_for("produits_odg", array('odg' => $odg)) ?>">Produits</a></li>
    <li><a href="<?php echo url_for("produits_odg", array('odg' => $odg)) ?>"><?php echo $odg ?></a></li>
    <li class="active"><a href="<?php echo url_for("produits_odg", array('odg' => $odg, 'date' => $date)) ?>"><?php echo $date ?></a></li>
</ol>

<img src="/odg/images/pdf/logo_<?php echo strtolower($odg) ?>.jpg" style="position: absolute; right: 15px; height: 100px;" />
<h2><?php echo $odgInfos['nom'] ?></h2>

<div class="row">
  <div class="col-xs-2">Adresse : </div>
  <div class="col-xs-5"><?php echo $odgInfos['adresse'] ?></div>
</div>
<div class="row">
  <div class="col-xs-2">Téléphone : </div>
  <div class="col-xs-5"><?php echo $odgInfos['telephone'] ?></div>
</div>
<div class="row">
  <div class="col-xs-2">Mail : </div>
  <div class="col-xs-5"><?php echo $odgInfos['email'] ?></div>
</div>
<?php if(isset($odgInfos['email_notification'])): ?>
<div class="row">
  <div class="col-xs-2">Mail de notification : </div>
  <div class="col-xs-5"><?php echo $odgInfos['email_notification'] ?></div>
</div>
<?php endif; ?>

<div class="row">
 
  <div class="col-xs-10">
     
<a class="pull-right btn btn-default" href="/odg/exports/<?php echo $odg ?>"><span class="glyphicon glyphicon-file">&nbsp;</span>Exports</a>
</div>
<?php if(isset($odgInfos['inao'])): ?>
  <div class="col-xs-2">
      <a href="<?php echo url_for('produit_habilitation',  array('odg' => $odg)) ?>" class="btn btn-default pull-right" ><span class="glyphicon glyphicon-file">&nbsp;</span>Habilitation</a>
<?php endif; ?>
</div>
</div>

<h3 style="margin-top: 40px;">Produits</h3>

<table class="table table-condensed table-striped table-bordered">
    <thead>
        <?php include_partial('produit/itemHeader', array('notDisplayDroit' => $notDisplayDroit)) ?>
    </thead>
    <tbody>
    <?php foreach($produits as $produit): ?>
        <?php include_component('produit', 'item', array('produit' => $produit, 'date' => $date, 'supprimable' => false, 'notDisplayDroit' => $notDisplayDroit)) ?>
    <?php endforeach; ?>
    </tbody>
</table>
