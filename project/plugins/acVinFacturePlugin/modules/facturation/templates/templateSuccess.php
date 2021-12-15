<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
  <li class="active"><a href="">Template de facturation</a></li>
  <li class="active"><a href=""><?php echo $template->libelle ?></a></li>
</ol>

<h2><?php echo $template->libelle ?></h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Libellé</th>
            <th>Compta</th>
            <th>Type</th>
            <th>Prix</th>
            <th>Document</th>
            <th>Calcul de la Quantité</th>
            <th>Unité</th>
        </tr>
    </thead>
<?php foreach($template->cotisations as $cotisation): ?>
    <?php foreach($cotisation->details as $detail): ?>
    <tr>
        <td><?php echo $cotisation->libelle ?> <?php echo $detail->libelle ?></td>
        <td><?php echo $cotisation->code_comptable ?></td>
        <td><?php echo str_replace("Cotisation", "", $detail->modele) ?></td>
        <td class="text-right"><?php echo $detail->prix ?>&nbsp;€</td>
        <td><?php echo implode(",&nbsp;", $detail->docs->getRawValue()->toArray()) ?></td>
        <td><?php echo $detail->callback ?><?php if($detail->exist('callback_parameters')): ?> <small class="text-muted"><?php echo implode(", ", $detail->callback_parameters->getRawValue()->toArray()) ?></small><?php endif; ?></td>
        <td class="text-left"><?php if($detail->exist('unite')): ?><?php echo $detail->unite ?><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
<?php endforeach; ?>
</table>
