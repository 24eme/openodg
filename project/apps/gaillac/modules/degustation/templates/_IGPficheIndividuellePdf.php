<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Date'); ?>
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

.size-cepage {
    height: 25px;
}

.size-commentaire {
    height: 70px;
}

.fond-sombre {
    background-color: grey;
}

.encart-nom {
    padding: 0px;
    margin: 0px;
}

.text-muted {
    color: white;
}


</style>
<div class="encart-nom"><small>NOM & PRENOM DU DEGUSTATEUR : </small>............................................................................................................................................................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small>SIGNATURE :</small></div>

<table border=0 cellspacing=0 cellpadding=0>
    <tr>
        <td colspan="2"><b>DATE&nbsp;:&nbsp;<?php echo format_date($lots[0]->date_commission, "dd/MM/yyyy", "fr_FR"); ?><br>JURY&nbsp;N°&nbsp;:&nbsp;<?php echo $lots[0]->numero_table; ?><br></b></td>
        <?php foreach ($lots as $lot) :?>
            <td class="td text-large <?php if(! $lot): ?>text-muted<?php endif;?>" colspan="3">&nbsp;N°&nbsp;échantillon&nbsp;:&nbsp;<?php if ($lot){ echo $lot->numero_anonymat;} else {echo "";} ?><br>&nbsp;<?php if ($lot) {echo $lot->getProduitLibelle();} elseif(isset($lot->cepages)) {echo $lot->getCepagesLibelle();} ?></td>
        <?php endforeach;?>
    </tr>
    <tr>
        <td class="td text-large" colspan="2">&nbsp;<b>ASPECT&nbsp;VISUEL</b></td>
        <td class="td fond-sombre" colspan="12">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Limpidité</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Oui<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Limite<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Non<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Nuance&nbsp;brune/marron **</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Oui<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Limite<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Non<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td align-right" colspan="2"><small><i>&nbsp;(notes oxydation / évolution)&nbsp;&nbsp;</i></small></td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td class="td text-large" colspan="2">&nbsp;<b>ASPECT&nbsp;OLFACTIF</b></td>
        <td class="td fond-sombre" colspan="12">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Franchise</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Oui<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Limite<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Non<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td align-right" colspan="2"><small><i>&nbsp;Préciser&nbsp;le&nbsp;type&nbsp;de&nbsp;défaut&nbsp;:&nbsp;&nbsp;&nbsp;</i></small></td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Intensité</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Forte<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Moyenne<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Faible<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td" colspan="14">&nbsp;<small><i><b><u>Si&nbsp;revendiqué&nbsp;:</u></b></i></small></td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Caractère&nbsp;primeur</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Oui<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Limite<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Non<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Typicité&nbsp;cépage</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Oui<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Limite<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Non<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td text-large" colspan="2">&nbsp;<b>ASPECT&nbsp;GUSTATIF</b></td>
        <td class="td fond-sombre" colspan="12">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Franchise</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Oui<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Limite<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Non<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td align-right" colspan="2"><small><i>&nbsp;Préciser&nbsp;le&nbsp;type&nbsp;de&nbsp;défaut&nbsp;:&nbsp;&nbsp;&nbsp;</i></small></td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Equilibre</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Oui<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Limite<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Non<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td align-right" colspan="2">&nbsp;<small><i>Si&nbsp;déséquilibre,&nbsp;précisez&nbsp;:&nbsp;&nbsp;&nbsp;</i></small></td>
        <?php foreach ($lots as $lot): ?>
            <td class="td align-mid" colspan="3"><?php if($lot): ?><i><small>Acide&nbsp;&nbsp;Plat&nbsp;&nbsp;Amer&nbsp;&nbsp;Astringent&nbsp;&nbsp;Alcooleux</small></i><?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Volume</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Bon<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Moyen<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Fluide<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Qualité&nbsp;des&nbsp;tanins</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if( ($lot) && stripos($lot->getCouleurLibelle(), 'rouge')): ?>Bon<?php endif;?></td>
            <td class="td"><?php if( ($lot) && stripos($lot->getCouleurLibelle(), 'rouge')): ?>Moyen<?php endif;?></td>
            <td class="td"><?php if( ($lot) && stripos($lot->getCouleurLibelle(), 'rouge')): ?>Mauvais<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td" colspan="2">&nbsp;<small><i><b><u>Si&nbsp;revendiqué&nbsp;:</u></b></i></small></td>
        <td class="td" colspan="12">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Caractère&nbsp;primeur</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Oui<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Limite<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Non<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-middle-size" colspan="2"><b>Typicité&nbsp;cépage</b>&nbsp;&nbsp;</td>
        <?php foreach ($lots as $lot): ?>
            <td class="td"><?php if($lot): ?>Oui<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Limite<?php endif;?></td>
            <td class="td"><?php if($lot): ?>Non<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td text-middle-size" colspan="2">&nbsp;<b>APPRECIATION&nbsp;GENERALE</b></td>
        <td colspan="12" class="td align-mid text-middle-size"><b>C :</b> Conforme <b>- NCMi :</b> niveau faible ou défaut organoleptique de très faible intensité  (manquement mineur) <b>- NCMa :</b> défaut organoleptique non rédhibitoire, défaut réversible : réduction, oxydation, pas net, couleur, ... (manquement majeur) - <b>NCG :</b> Non Conforme présentant un défaut rédhibitoire correspondant à un manquement grave</td>
    </tr>
    <tr>
        <td class="td align-mid text-middle-size" colspan="2">&nbsp;<b>AGRÉMENT&nbsp;CÉPAGE&nbsp;*</b></td>
        <?php foreach ($lots as $lot): ?>
            <td class="td align-mid" colspan="3"><?php if($lot): ?>OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td align-mid text-middle-size" colspan="2">&nbsp;<b>AGRÉMENT&nbsp;PRIMEUR&nbsp;*</b></td>
        <?php foreach ($lots as $lot): ?>
            <td class="td align-mid" colspan="3"><?php if($lot):?>OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON<?php endif;?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="td align-mid text-middle-size" colspan="2">&nbsp;<b>C&nbsp;-&nbsp;NCMI&nbsp;-&nbsp;NCMa&nbsp;-&nbsp;NCG&nbsp;</b></td>
        <td class="td align-mid" colspan="3"></td>
        <td class="td align-mid" colspan="3"></td>
        <td class="td align-mid" colspan="3"></td>
        <td class="td align-mid" colspan="3"></td>
    </tr>
    <tr>
        <td class="td align-mid size-commentaire" colspan="2"><b><i>COMMENTAIRES <small>(Obligatoires)</small></i></b></td>
        <td class="td align-mid size-commentaire" colspan="3"></td>
        <td class="td align-mid size-commentaire" colspan="3"></td>
        <td class="td align-mid size-commentaire" colspan="3"></td>
        <td class="td align-mid size-commentaire" colspan="3"></td>
    </tr>

</table>
<div>
    <small><i>* Rayer la mention inutile</i></small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small><i>** Brune pour les vins rouges, marron pour les vins blancs</i></small>
</div>
