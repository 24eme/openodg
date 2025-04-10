<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>

.td, .th {
    border: 1px solid #000;
}

</style>
<table >
    <tr>
        <td class="td" colspan="2" rowSpan="6">Numéros des échantillons</td>
        <td class="td" colspan="7">Noms des Dégustateurs</td>
        <td class="td" colspan="5"></td>
        <td class="td" colspan="2"></td>
    </tr>
    <tr>
        <?php for($i = 0;$i<7;$i++): ?>
            <td class="td" rowSpan="4">nom</td>
        <?php endfor; ?>
        <td class="td" colspan="5" rowSpan="4">Commentaires</td>
        <td class="td" rowSpan="6">Décision</td>
    </tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr>
        <td class="td" colspan="7">Notes</td>
    </tr>
</table>
