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

.text-huit-pt {
    font-size: 8pt;
}

.text-dix-pt {
    font-size: 10pt;
}

.text-six-pt {
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

</style>



<div class="encart-nom"><small>NOM & PRENOM DU DEGUSTATEUR : </small>............................................................................................................................................................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small>SIGNATURE :</small></div>

<table border=0 cellspacing=0 cellpadding=0>
    <tr>
        <td colspan="2"><b>DATE&nbsp;:&nbsp;<br>JURY&nbsp;N°&nbsp;:<br><span class="text-red">AOC</span></b></td>
        <td class="td text-dix-pt" colspan="3">&nbsp;N°&nbsp;échantillon&nbsp;:<br>&nbsp;Cépage&nbsp;:<br>&nbsp;<small><i>(si revendiqué)</i></small></td>
        <td class="td text-dix-pt" colspan="3">&nbsp;N°&nbsp;échantillon&nbsp;:<br>&nbsp;Cépage&nbsp;:<br>&nbsp;<small><i>(si revendiqué)</i></small></td>
        <td class="td text-dix-pt" colspan="3">&nbsp;N°&nbsp;échantillon&nbsp;:<br>&nbsp;Cépage&nbsp;:<br>&nbsp;<small><i>(si revendiqué)</i></small></td>
        <td class="td text-dix-pt" colspan="3">&nbsp;N°&nbsp;échantillon&nbsp;:<br>&nbsp;Cépage&nbsp;:<br>&nbsp;<small><i>(si revendiqué)</i></small></td>
    </tr>
    <tr>
        <td class="td text-dix-pt" colspan="2">&nbsp;<b>ASPECT&nbsp;VISUEL</b></td>
        <td class="td fond-sombre" colspan="12">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Limpidité</b>&nbsp;&nbsp;</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Nuance&nbsp;brune/marron</b>&nbsp;&nbsp;</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
    </tr>
    <tr>
        <td class="td align-right" colspan="2"><small><i>&nbsp;(notes oxydation / évolution)&nbsp;&nbsp;</i></small></td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td class="td text-dix-pt" colspan="2">&nbsp;<b>ASPECT&nbsp;OLFACTIF</b></td>
        <td class="td fond-sombre" colspan="12">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Franchise</b>&nbsp;&nbsp;</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
    </tr>
    <tr>
        <td class="td align-right" colspan="2"><small><i>&nbsp;Préciser&nbsp;le&nbsp;type&nbsp;par&nbsp;défaut&nbsp;:&nbsp;&nbsp;&nbsp;</i></small></td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Intensité</b>&nbsp;&nbsp;</td>
        <td class="td">Forte</td>
        <td class="td">Moyenne</td>
        <td class="td">Faible</td>
        <td class="td">Forte</td>
        <td class="td">Moyenne</td>
        <td class="td">Faible</td>
        <td class="td">Forte</td>
        <td class="td">Moyenne</td>
        <td class="td">Faible</td>
        <td class="td">Forte</td>
        <td class="td">Moyenne</td>
        <td class="td">Faible</td>
    </tr>
    <tr>
        <td class="td" colspan="14">&nbsp;<small><i><b><u>Si&nbsp;revendiqué&nbsp;:</u></b></i></small></td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Caractère&nbsp;primeur</b>&nbsp;&nbsp;</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Typicité&nbsp;cépage</b>&nbsp;&nbsp;</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
    </tr>
    <tr>
        <td class="td text-dix-pt" colspan="2">&nbsp;<b>ASPECT&nbsp;GUSTATIF</b></td>
        <td class="td fond-sombre" colspan="12">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Franchise</b>&nbsp;&nbsp;</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
    </tr>
    <tr>
        <td class="td align-right" colspan="2"><small><i>&nbsp;Préciser&nbsp;le&nbsp;type&nbsp;par&nbsp;défaut&nbsp;:&nbsp;&nbsp;&nbsp;</i></small></td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
        <td class="td" colspan="3">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Equilibre</b>&nbsp;&nbsp;</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
    </tr>
    <tr>
        <td class="td align-right" colspan="2">&nbsp;<small><i>Si&nbsp;déséquilibre,&nbsp;précisez&nbsp;:&nbsp;&nbsp;&nbsp;</i></small></td>
        <td class="td align-mid" colspan="3"><i><small>Acide&nbsp;&nbsp;Plat&nbsp;&nbsp;Amer&nbsp;&nbsp;Astringent&nbsp;&nbsp;Alcooleux</small></i></td>
        <td class="td align-mid" colspan="3"><i><small>Acide&nbsp;&nbsp;Plat&nbsp;&nbsp;Amer&nbsp;&nbsp;Astringent&nbsp;&nbsp;Alcooleux</small></i></td>
        <td class="td align-mid" colspan="3"><i><small>Acide&nbsp;&nbsp;Plat&nbsp;&nbsp;Amer&nbsp;&nbsp;Astringent&nbsp;&nbsp;Alcooleux</small></i></td>
        <td class="td align-mid" colspan="3"><i><small>Acide&nbsp;&nbsp;Plat&nbsp;&nbsp;Amer&nbsp;&nbsp;Astringent&nbsp;&nbsp;Alcooleux</small></i></td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Volume</b>&nbsp;&nbsp;</td>
        <td class="td">Bon</td>
        <td class="td">Moyen</td>
        <td class="td">Fluide</td>
        <td class="td">Bon</td>
        <td class="td">Moyen</td>
        <td class="td">Fluide</td>
        <td class="td">Bon</td>
        <td class="td">Moyen</td>
        <td class="td">Fluide</td>
        <td class="td">Bon</td>
        <td class="td">Moyen</td>
        <td class="td">Fluide</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Qualité&nbsp;des&nbsp;tanins</b>&nbsp;&nbsp;</td>
        <td class="td">Bon</td>
        <td class="td">Moyen</td>
        <td class="td">Mauvais</td>
        <td class="td">Bon</td>
        <td class="td">Moyen</td>
        <td class="td">Mauvais</td>
        <td class="td">Bon</td>
        <td class="td">Moyen</td>
        <td class="td">Mauvais</td>
        <td class="td">Bon</td>
        <td class="td">Moyen</td>
        <td class="td">Mauvais</td>
    </tr>
    <tr>
        <td class="td" colspan="2">&nbsp;<small><i><b><u>Si&nbsp;revendiqué&nbsp;:</u></b></i></small></td>
        <td class="td" colspan="12">&nbsp;</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Caractère&nbsp;primeur</b>&nbsp;&nbsp;</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
    </tr>
    <tr class="align-mid">
        <td class="td align-right text-huit-pt" colspan="2"><b>Typicité&nbsp;cépage</b>&nbsp;&nbsp;</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
        <td class="td">Oui</td>
        <td class="td">Limite</td>
        <td class="td">Non</td>
    </tr>
    <tr>
        <td class="td text-huit-pt" colspan="2">&nbsp;<b>APPRECIATION&nbsp;GENERALE</b></td>
        <td colspan="12" class="td align-mid text-huit-pt"><b><u>A:</b></u> accepté <b>; <u>AD:</u></b> accepté avec défaut(s) non rédhibitoire(s) <b>; <u>NAE</u> (vrac uniquement):</b> non accepté en l'état, présence de défaut(s) d'intensité moyenne à forte corrigeable(s) <b>; <u>NA:</u></b> non accepté, vins à défaut(s) rédhibitoire(s)</td>
    </tr>
    <tr>
        <td class="td align-mid text-huit-pt" colspan="2">&nbsp;<b>NOTE&nbsp;(obligatoire)</b></td>
        <td class="td align-mid" colspan="3"></td>
        <td class="td align-mid" colspan="3"></td>
        <td class="td align-mid" colspan="3"></td>
        <td class="td align-mid" colspan="3"></td>
    </tr>
    <tr>
        <td class="td align-mid text-huit-pt" colspan="2"><b>TYPICITE</b></td>
        <td class="td align-mid" colspan="3">OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LIMITE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON</td>
        <td class="td align-mid" colspan="3">OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LIMITE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON</td>
        <td class="td align-mid" colspan="3">OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LIMITE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON</td>
        <td class="td align-mid" colspan="3">OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LIMITE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON</td>
    </tr>
    <tr>
        <td class="td align-mid text-huit-pt" colspan="2">&nbsp;<b>AGRÉMENT&nbsp;PRIMEUR&nbsp;*</b></td>
        <td class="td align-mid" colspan="3">OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON</td>
        <td class="td align-mid" colspan="3">OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON</td>        <td class="td align-mid" colspan="3">OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON</td>        <td class="td align-mid" colspan="3">OUI&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NON</td>
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
    <small><i>* Rayer la mention inutile</i></small>
</div>
