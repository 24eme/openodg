<img src="/images/pdf/logo_<?php echo strtolower($odg) ?>.jpg" style="float: right; height: 100px;" />
<h2><?php echo $odgInfos['nom'] ?></h2>

<?php echo $odgInfos['adresse'] ?>

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
