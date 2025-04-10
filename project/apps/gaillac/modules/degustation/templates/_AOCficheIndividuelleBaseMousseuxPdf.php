<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>

.td, .th {
    border: 1px solid #000;
}

.align-right {
    text-align: right;
}

.align-left {
    text-align: left;
}

.align-mid {
    text-align: center;
}

.text-red {
    color: red;
}

.text-blue {
    color: blue;
}

.text-middle-size {
    font-size: 8pt;
}

.text-large {
    font-size: 10pt;
}

.text-small {
    font-size: 6pt;
}

.size-small {
    height: 30px;
}

.size-large {
    height: 90px;
}

.fond-sombre {
    background-color: grey;
}

.encart-nom {
    padding: 0px;
    margin: 0px;
}

.size-moyenne {
    height: 70px;
}

.text-muted {
    color: white;
}

</style>


<div class="encart-nom"><small>NOM & PRENOM DU DEGUSTATEUR : </small>............................................................................................................................................................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small>SIGNATURE :</small></div>

<table border=0 cellspacing=0 cellpadding=0>
    <tr>
        <td colspan="2"><b>DATE&nbsp;:&nbsp;<?php echo $lots[0]->date_commission; ?><br>JURY&nbsp;N°&nbsp;:<br><span class="text-blue">AOC MOUSSEUX</span></b></td>
        <?php foreach ($lots as $lot) :?>
            <td class="td text-large <?php if(! $lot): ?>text-muted<?php endif;?>" colspan="3">&nbsp;N°&nbsp;échantillon&nbsp;:&nbsp;<?php if ($lot){ echo $lot->numero_anonymat;} else {echo "";} ?><br>&nbsp;Cépage&nbsp;:&nbsp;<?php if ($lot) {echo $lot->getCepagesLibelle();} else {echo "";} ?><br>&nbsp;<small><i>(si revendiqué)</i></small></td>
        <?php endforeach; ?>
        </tr>
        <tr>
            <td class="td text-large" colspan="2">&nbsp;<b>ASPECT&nbsp;VISUEL</b></td>
            <td class="td fond-sombre" colspan="12">&nbsp;</td>
        </tr>
        <tr class="align-mid">
            <td class="td align-right text-middle-size" colspan="2"><b>Note d'évolution</b>&nbsp;&nbsp;</td>
            <?php foreach ($lots as $lot): ?>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Non</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">&nbsp;</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Oui</td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td class="td text-large" colspan="2">&nbsp;<b>ASPECT&nbsp;OLFACTIF</b></td>
            <td class="td fond-sombre" colspan="12">&nbsp;</td>
        </tr>
        <tr class="align-mid">
            <td class="td align-right text-middle-size" colspan="2"><b>Franchise</b>&nbsp;&nbsp;</td>
            <?php foreach ($lots as $lot): ?>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Oui</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>"></td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Non</td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td class="td align-right size-small" colspan="2"><small><i>&nbsp;Préciser&nbsp;le&nbsp;type&nbsp;de&nbsp;défaut&nbsp;:&nbsp;&nbsp;&nbsp;</i></small></td>
            <td class="td size-small" colspan="3">&nbsp;</td>
            <td class="td size-small" colspan="3">&nbsp;</td>
            <td class="td size-small" colspan="3">&nbsp;</td>
            <td class="td size-small" colspan="3">&nbsp;</td>
        </tr>
        <tr class="align-mid">
            <td class="td align-right text-middle-size" colspan="2"><b>Qualité&nbsp;aromatique</b>&nbsp;&nbsp;</td>
            <?php foreach ($lots as $lot): ?>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Bonne</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Moyenne</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Mauvaise</td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td class="td text-large" colspan="2">&nbsp;<b>ASPECT&nbsp;GUSTATIF</b></td>
            <td class="td fond-sombre" colspan="12">&nbsp;</td>
        </tr>
        <tr class="align-mid">
            <td class="td align-right text-middle-size" colspan="2"><b>Note&nbsp;d'évolution</b>&nbsp;&nbsp;</td>
            <?php foreach ($lots as $lot): ?>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Non</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>"></td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Oui</td>
            <?php endforeach; ?>
        </tr>
        <tr class="align-mid">
            <td class="td align-right text-middle-size" colspan="2"><b>Qualité&nbsp;aromatique</b>&nbsp;&nbsp;</td>
            <?php foreach ($lots as $lot): ?>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Bonne</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Moyenne</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Mauvaise</td>
            <?php endforeach; ?>
        </tr>
        <tr class="align-mid">
            <td class="td align-right text-middle-size" colspan="2"><b>Support acide</b>&nbsp;&nbsp;</td>
            <?php foreach ($lots as $lot): ?>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Bon</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Moyen</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Mauvais</td>
            <?php endforeach; ?>
        </tr>
        <tr class="align-mid">
            <td class="td align-right text-middle-size" colspan="2"><b>Typicité</b>&nbsp;&nbsp;</td>
            <?php foreach ($lots as $lot): ?>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Oui</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Limite</td>
                <td class="td <?php if(! $lot): ?>text-muted<?php endif;?>">Non</td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td class="td align-mid text-red text-middle-size size-moyenne" colspan="2">&nbsp;<b>NOTE&nbsp;(obligatoire)</b></td>
            <td class="td align-mid" colspan="3"></td>
            <td class="td align-mid" colspan="3"></td>
            <td class="td align-mid" colspan="3"></td>
            <td class="td align-mid" colspan="3"></td>
        </tr>
        <tr>
            <td class="td align-mid size-large" colspan="2"><b><i>COMMENTAIRES</b> <small class="text-small">(Obligatoires, précisez  le ou les défauts des vins classés DM et R)</small></i></td>
            <td class="td align-mid size-large" colspan="3"></td>
            <td class="td align-mid size-large" colspan="3"></td>
            <td class="td align-mid size-large" colspan="3"></td>
            <td class="td align-mid size-large" colspan="3"></td>
        </tr>
        <tr>
            <td class="td align-mid text-small" colspan="14">NB : Echelle de note:  <b>A</b> = Accepté / <b>AD</b> = Accepté avec défaut / NAE (vrac) = non accepté en l'état (ex DM) /  NA =  Non accepté (ex R) refusé, vin à défaut</td>
        </tr>
        <tr>
            <td class="td align-mid text-small" colspan="14"><b>Pour les vins en vrac uniquement: DM: refusée en l'état, défaut à corriger.</b></td>
        </tr>

    </table>
