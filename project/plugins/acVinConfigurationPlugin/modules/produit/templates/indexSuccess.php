<ol class="breadcrumb">
    <li><a href="<?php echo url_for("produits") ?>">Produits</a></li>
    <li class="active"><a href="<?php echo url_for("produits", array('date' => $date)) ?>"><?php echo $date ?></a></li>
    <li class="text-muted"><?php echo $config->_id ?><small>@<?php echo $config->_rev ?></small></li>
</ol>

<div style="position: relative;">
    <img src="/<?php echo $organisme->getLogoPdfWebPath() ?>" class="pull-right" />
    <h2><?php echo $organisme->getNom(); ?></h2>
    <div class="row">
      <div class="col-xs-1">Adresse : </div>
      <div class="col-xs-7"><?php echo $organisme->getAdresse(); ?><br /><?php echo $organisme->getCodePostal(); ?> <?php echo $organisme->getCommune() ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">Téléphone&nbsp;:</div>
      <div class="col-xs-7"><?php echo $organisme->getTelephone(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">Mail : </div>
      <div class="col-xs-7"><?php echo $organisme->getEmail(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">OI :</div>
      <div class="col-xs-7"><?php echo $organisme->getOi(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">Responsable&nbsp;:</div>
      <div class="col-xs-7"><?php echo $organisme->getResponsable(); ?></div>
    </div>
    <?php if (file_exists($organisme->getImageSignaturePath())): ?>
    <div class="row">
      <div class="col-xs-1">Signature&nbsp;:</div>
      <div class="col-xs-7"><img src="/<?php echo $organisme->getImageSignatureWebPath() ?>" /></div>
    </div>
    <?php endif; ?>
</div>

<h2>Facturation</h2>
<a href="<?php echo url_for('facturation_template_last'); ?>">Voir le template de facturation</a>

<h2>Produits <a href="<?php echo url_for('produit_nouveau') ?>" class="btn btn-sm btn-default pull-right"><span class="glyphicon glyphicon-plus"></span> Ajouter un produit</a></h2>

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
<?php if(class_exists("Parcellaire") && in_array('parcellaire', sfConfig::get('sf_enabled_modules'))): ?>
<h2>Les Aires</h2>
<table class="table table-condensed table-striped table-bordered">
    <thead>
    <tr>
        <th class="col-xs-10">Dénomination libellé</th>
        <th class="col-xs-1 text-center">Identifiant INAO</th>
        <th class="col-xs-1 text-center">Couleur</th>
    </tr>
    </thead>
    <tbody>
<?php foreach(ParcellaireConfiguration::getInstance()->getAiresInfos() as $aire): ?>
    <tr>
        <td><?php echo $aire['name'];  ?></td>
        <td class="text-center"><a href="https://www.opendatawine.fr/denominations/<?php echo $aire['denomination_id']; ?>.html"><?php echo $aire['denomination_id']; ?></a></td>
        <td class="text-center"><span style="background-color: <?php echo $aire['color']; ?>"> &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp; </span></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php include_partial('produit/ouverturesTeledeclarations'); ?>
