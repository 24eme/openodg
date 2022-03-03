<ol class="breadcrumb">
    <li><a href="<?php echo url_for("produits") ?>">Produits</a></li>
    <li class="active"><a href="<?php echo url_for("produits", array('date' => $date)) ?>"><?php echo $date ?></a></li>
    <li class="text-muted"><?php echo $config->_id ?><small>@<?php echo $config->_rev ?></small></li>
</ol>

<?php slot('global_css_class', 'no_right_col')?>

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
