<?php if (count($synthese->getRawValue())): ?>
<h3 class="mt-0" id="synthese_cepage">
    Synthèse par cépages
<?php if (ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration()): ?>
    <small>des produits reconnus au CVI</small>
<?php endif; ?>
</h3>

<table class="table table-bordered table-condensed table-striped tableParcellaire">
  <thead>
    <tr>
        <th class="col-xs-8">Cépage <small class="text-muted">(<abbr title="de la campagne <?php echo ParcellaireConfiguration::getInstance()->getCampagneJeunesVignes(); ?> à <?php echo ConfigurationClient::getInstance()->getCampagneParcellaire()->getCurrent(); ?>">jeunes vignes</abbr> séparées)</small></th>
        <th class="col-xs-4 text-center" colspan="2">Superficie <span class="text-muted small"><?php echo (ParcellaireConfiguration::getInstance()->isAres()) ? "(a)" : "(ha)" ?></span></th>
    </tr>
  </thead>
  <tbody>
<?php

    foreach($synthese as $cepage_libelle => $s): ?>
        <tr>
            <td><?php echo $cepage_libelle ; ?></td>
            <td class="text-right"><?php echoSuperficie($s['superficie']); ?></td>
        </tr>
<?php
    endforeach;
?>
    <tr>
        <td><strong>Total <?php if(isset($coop) && $coop): ?>de le coopérative<?php endif; ?></strong></td>
        <td class="text-right"><strong><?php echoSuperficie(array_sum(array_column($synthese->getRawValue(), 'superficie'))); ?></strong></td>
    </tr>
  </tbody>
</table>
<?php endif; ?>
