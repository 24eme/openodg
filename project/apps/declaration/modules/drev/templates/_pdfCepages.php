<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>

<style>
<?php echo styleDRev(); ?>
</style>

<span style="text-align: center; font-size: 12pt; font-weight:bold;">Déclaration des cépages</span>
<br /><br />

<?php if($drev->hasDR()): ?>
<i>Ces volumes ceux issus de la déclaration de récolte, usages industriels inclus.</i>
<br />
<?php endif; ?>

<?php
foreach ($drev->getProduitsCepageByAppellations() as $produitsCepageByAppellations):
    if(count($produitsCepageByAppellations->cepages)):
    ?>
    <div><span class="h3">&nbsp;<?php echo $produitsCepageByAppellations->appellation->getLibelle() ?>&nbsp;</span></div>

    <table border="1" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
        <tr>
            <th class="th" style="text-align: left; width: 267px">&nbsp;Produits</th>        
            <th class="th" style="text-align: center; width: 120px">Hors VT/SGN</th>
            <th class="th" style="text-align: center; width: 120px">VT</th>
            <th class="th" style="text-align: center; width: 120px">SGN</th>   
        </tr>
        <?php
        foreach ($produitsCepageByAppellations->cepages as $cepages):
            ?>
            <tr>
                <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $cepages->libelle ?></td>

                <?php if ($cepages->exist('volume_revendique') && $cepages->volume_revendique): ?>
                    <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo sprintFloatFr($cepages->volume_revendique) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
                <?php else: ?>
                    <td class="tdAlt"><?php echo tdStart() ?>&nbsp;</td>
                <?php endif; ?>
                <?php if ($cepages->exist('volume_revendique_vt') && $cepages->volume_revendique_vt): ?>
                    <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo sprintFloatFr($cepages->volume_revendique_vt) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
                <?php else: ?>
                    <td class="tdAlt"><?php echo tdStart() ?>&nbsp;</td>
                <?php endif; ?>
                <?php if ($cepages->exist('volume_revendique_sgn') && $cepages->volume_revendique_sgn): ?>
                    <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo sprintFloatFr($cepages->volume_revendique_sgn) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
                <?php else: ?>
                    <td class="tdAlt"><?php echo tdStart() ?>&nbsp;</td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>     
    </table>
    <br />
    <?php
    endif;
endforeach;
?>