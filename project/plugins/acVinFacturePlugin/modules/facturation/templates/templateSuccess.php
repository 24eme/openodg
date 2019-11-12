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
            <th class="col-xs-1">Prix</th>
        </tr>
    </thead>
<?php foreach($template->cotisations as $cotisation): ?>
    <tr>
        <th><?php echo $cotisation->libelle ?> <span class="text-muted">(<?php echo $cotisation->code_comptable ?>)</span></th>
        <th></th>
    </tr>
    <?php foreach($cotisation->details as $detail): ?>
    <tr>
        <td><?php echo $detail->libelle ?></td>
        <td class="text-right"><?php echo $detail->prix ?> €</td>
    </tr>
    <?php endforeach; ?>
<?php endforeach; ?>
</table>
