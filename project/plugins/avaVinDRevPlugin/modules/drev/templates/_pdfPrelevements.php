<?php use_helper("Date"); ?>
<?php use_helper("TemplatingPDF"); ?>
<?php use_helper("Float"); ?>

<div><span class="h3">&nbsp;Prélèvement&nbsp;</span></div>
<table border="1" cellspacing=0 cellpadding=0 style="text-align: right; border: 1px solid #c75268;">
    <tr>
        <th class="th" style="text-align: left; width: 258px">&nbsp;Produit</th>
        <th class="th" style="text-align: left; width: 260px">&nbsp;Date <small>(à partir de laquelle le vin est prêt à être dégusté)</small>&nbsp;</th>
        <th class="th" style="text-align: center; width: 121px">Lots</th>
    </tr>
    <?php if(count($drev->getPrelevementsByDate($type)) > 0): ?>
        <?php foreach($drev->getPrelevementsByDate($type) as $prelevement): ?>
        <tr>
            <td class="td" style="text-align: left"><?php echo tdStart() ?>&nbsp;<?php echo $prelevement->libelle_produit ?></td>
            <td class="td" style="text-align: left"><?php echo tdStart() ?>&nbsp;<?php echo format_date($prelevement->date, "D", "fr_FR") ?></td>
            <td class="td" style="text-align: right"><?php echo tdStart() ?><?php if($prelevement->total_lots): ?><?php echo $prelevement->total_lots ?>&nbsp;<small>lot (s)</small>&nbsp;&nbsp;&nbsp;<?php else: ?>&nbsp;<?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
          <td colspan="3" class="td" style="text-align: center;"><?php echo tdStart() ?>&nbsp;<i>Aucun prélèvement prévu</i></td>
        </tr>
    <?php endif; ?>
</table>
<small>&nbsp;</small>
<?php if($drev->chais->exist($type)): ?>
  <?php $chai = $drev->chais->get($type) ?>
  <div><span class="h3Alt">&nbsp;Lieu de prélèvement&nbsp;</span></div>
  <table class="tableAlt"><tr><td>
    <table border="0">
        <tr>
            <td style="height:22px;" colspan="2"><?php echo tdStart() ?>&nbsp;<i><?php echo $chai->adresse ?>, <?php echo $chai->code_postal ?>, <?php echo $chai->commune ?></i></td>
        </tr>
    </table>
  </td></tr></table>
<?php endif; ?>