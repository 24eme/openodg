<?php ?>
<?php if (count($synthese->getRawValue())): ?>
<h3 id="synthese_cepage">
    Synthèse par cépages
<?php if (ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration()): ?>
    des produits reconnus au CVI
<?php endif; ?>
</h3>

<table class="table table-bordered table-condensed table-striped tableParcellaire">
  <thead>
    <tr>
        <th class="col-xs-4">Cépage <small class="text-muted">(jeunes vignes séparées)</small></th>
        <th class="col-xs-4 text-center" colspan="2">Superficie <span class="text-muted small"><?php echo (ParcellaireConfiguration::getInstance()->isAres()) ? "(a)" : "(ha)" ?></span></th>
    </tr>
  </thead>
  <tbody>
<?php

    foreach($synthese as $cepage_libelle => $s): ?>
        <tr>
            <td><?php echo $cepage_libelle ; ?></td>
            <td class="text-right"><?php echoSuperficie($s['superficie']); ?></td>
<?php
    endforeach;
?>
    <tr>
        <td><strong>Total</strong></td>
        <td class="text-right"><strong><?php echoSuperficie(array_sum(array_column($synthese->getRawValue(), 'superficie'))); ?></strong></td>
    </tr>
  </tbody>
</table>
<?php endif;
