<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>

.td, .th {
    border: 1px solid #000;
    text-align: center;
}

.header-height {
    height: 100px;
}

.body-height {
    height: 50px;
}

.footer-height {
    height: 70px;
}

.text-align.left {
    text-align: left;
}


</style>
<table>
    <?php  $ligne=0; $table_first = true; $table_header = true;
    foreach($lots as $numAnonyme => $lotInfo): ?>
    <?php if($ligne == 13): $table_header = true; $ligne = 0; ?>
            </table>
            <br pagebreak="true" />
        <?php endif; ?>
    <?php if ($table_first || $table_header): $table_header = false; $table_first = false; ?>
        <table border=0 cellspacing=0 cellpadding=0>
            <tr>
                <td colspan="2"></td>
                <td class="td" colspan="7"><b>Noms des Dégustateurs</b></td>
                <td colspan="5"></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td class="td header-height" colspan="2"><br><br><br><b><small>Numéros des échantillons</small></b></td>
                <?php for($i = 0;$i<7;$i++): ?>
                    <td class="td header-height"></td>
                <?php endfor; ?>
                <td class="td header-height" colspan="5"><br><br><br><b>Commentaires</b></td>
                <td class="td header-height" colspan="2"><br><br><br><b>Décision</b></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td class="td" colspan="7"><b>Notes</b></td>
                <td colspan="8"></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td class="td body-height" colspan="2"><?php echo $numAnonyme; ?><br><small>&nbsp;<?php echo $lotInfo->produit_libelle ?>&nbsp;</small></td>
            <?php for($y=0;$y<7;$y++): ?>
                <td class="td body-height"></td>
            <?php endfor; ?>
            <td class="td body-height" colSpan="5"></td>
            <td class="td body-height" colSpan="2"></td>
        </tr>
        <?php $ligne++; ?>
    <?php endforeach; ?>
    <tr>
        <td class="td footer-height" colspan="2"><small><b>Signatures des dégustateurs</b></small><br>=></td>
        <?php for($y=0;$y<7;$y++): ?>
            <td class="td footer-height"></td>
        <?php endfor; ?>
        <td class="td footer-height text-align-left" colspan="7"><small><b>Nom et signature de l'animateur de la commission organoleptique : </b></small></td>
    </tr>
</table>
