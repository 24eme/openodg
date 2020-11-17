<?php use_helper('TemplatingPDF'); ?>
<style>
</style>
    <div><span class="h3">&nbsp;Etiquettes des prélévements&nbsp;</span></div>
    <table border="1" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php foreach($degustation->getEtiquettesFromLots() as $lot): ?>
        <tr>
          <?php for($i=0; $i <3 ; $i++): ?>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<strong><?php echo $degustation->volume;  ?></strong></td>
          <?php endfor; ?>
        </tr>
    <?php endforeach; ?>
    </table>
