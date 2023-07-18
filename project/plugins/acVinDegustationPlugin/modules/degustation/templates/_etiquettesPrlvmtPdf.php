<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<?php use_helper('Text') ?>
<style>
  .font-1-3em{
    /*font-size: 1.3em;*/
  }

  table.table-etiquette tr td {
       font-family: monospace;
       font-size: 11px;
  }
</style>

<?php $i = 0; ?>
<table cellspacing="0" cellpadding="0" style="height: 1122.4px; margin: 0; padding: 0">
<?php $planche = $plancheLots->getRawValue(); ?>
<?php foreach($planche as $lotInfo): ?>
    <?php for ($etiquette = 0; $etiquette < DegustationConfiguration::getInstance()->getNbEtiquettes(); $etiquette++): ?>
        <?php if ($i % 3 === 0): ?>
            <tr style="height: <?php echo 1122.4/count($plancheLots) ?>px; margin: 0; padding: 0;">
        <?php endif ?>

        <?php include_partial('degustation/etiquetteUnitairePDF', compact('lotInfo', 'i', 'anonymat4labo')) ?>

        <?php $i++ ?>

        <?php if ($i % 3 === 0): ?>
            </tr>
        <?php endif; ?>
    <?php endfor; ?>
<?php endforeach; ?>

<?php if ($i % 3 !== 0): ?>
    </tr>
<?php endif; ?>
</table>
