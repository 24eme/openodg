<?php use_helper('TemplatingPDF') ?>

<style>
<?php echo styleDRev(); ?>
</style>

<span style="text-align: center; font-size: 12pt; font-weight:bold;">Déclaration des lots susceptibles d'être prelévés</span>
<br /><br />

<?php foreach($drev->prelevements as $prelevement): ?>
<?php if(!count($prelevement->lots)): continue; endif; ?>
<div><span class="h3">&nbsp;<?php echo $prelevement->libelle_produit ?>&nbsp;</span></div>
<table border="1" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 357px">&nbsp;Produits</th>
        <th class="th" style="text-align: center; width: 140px">Hors VT/SGN</th>
        <th class="th" style="text-align: center; width: 140px">VT/SGN</th>
    </tr>
    <?php foreach($prelevement->lots as $lot): ?>
    <?php if(!count($prelevement->lots)): continue; endif; ?>
    <tr>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $lot->libelle ?></td>
        <?php if($lot->nb_hors_vtsgn): ?>
            <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo $lot->nb_hors_vtsgn ?>&nbsp;<small>lot (s)</small>&nbsp;&nbsp;&nbsp;</td>
        <?php else: ?>
            <td class="tdAlt"><?php echo tdStart() ?>&nbsp;</td>
        <?php endif; ?>
        <?php if($lot->nb_vtsgn): ?>
            <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo $lot->nb_vtsgn ?>&nbsp;<small>lot (s)</small>&nbsp;&nbsp;&nbsp;</td>
        <?php else: ?>
            <td class="tdAlt"><?php echo tdStart() ?>&nbsp;</td>
        <?php endif; ?>
    </tr>
    <?php endforeach; ?>
</table>
<br />
<?php endforeach; ?>