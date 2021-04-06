<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li></li>
</ol>
<?php use_helper('Float') ?>

<div class="page-header no-border">
</div>
<?php if (count($lotsStepsHistory)): ?>
  <div class="row">
    <table class="table table-condensed table-striped">
      <thead>
        <th class="col-sm-1">N° Lot</th>
        <th class="col-sm-1">N° Dossier</th>
        <th class="col-sm-1">Date</th>
        <th class="col-sm-3">Appellation</th>
        <th class="col-sm-1 text-center">Dossier</th>
        <th class="col-sm-1 text-center">Degust.</th>
        <th class="col-sm-1 text-center">Table</th>
        <th class="col-sm-1 text-center">Dégusté</th>
        <th class="col-sm-2 text-right"></th>
      </thead>
      <tbody>
        <?php foreach($lotsStepsHistory as $lotKey => $lot): ?>
              <tr>
                <td><?php echo $lot->numero_archive;  ?></td>
                <td><?php echo $lot->numero_dossier;  ?></td>
                <td ><strong><?php echo Date::francizeDate($lot->date); ?></strong></td>
                <td><?php echo "$lot->produit_libelle (<span class='text-muted')>$lot->millesime</span>)" ?></td>
                <td class="text-center">
                  <?php echo link_to(
                      strstr(str_replace($lot->numero_archive, '', $lotKey), '-', 1),
                      strtolower(strstr(str_replace($lot->numero_archive, '', $lotKey), '-', 1)).'/visualisation?id='.str_replace($lot->numero_archive, '', $lotKey)
                  ) ?>
                <td class="text-center" >

                </td>
                <td class="text-center">


                </td>
                <td class="text-center">

                    </td>
                    <td class=" text-right">

                  </td>
                  </tr>
            <?php endforeach; ?>
            <tbody>
            </table>
          <?php endif; ?>
        </div>
