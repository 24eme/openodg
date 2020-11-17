<?php use_helper('TemplatingPDF'); ?>
<style>
<?php echo styleDRev(); ?>
</style>
    <div><span class="h3">&nbsp;Engagement(s)&nbsp;</span></div>
    <table border="1" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php foreach($degustation->lots as $lot): ?>
        <tr>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<span style="font-family: Dejavusans">☑</span> <?php echo $lot->volume;  ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
