<?php use_helper('TemplatingPDF') ?>

<span style="background-color: grey; color: white; font-weight: bold;">Exploitation</span><br/>
<table style="border: 1px solid grey;"><tr><td>
<table border="0">
  <tr>
    <td style="width: 420px;">&nbsp;Nom : <i>Cave d'Actualys</i></td>
    <td>N° CVI : <i>7523700100</i></td>
  </tr>
  <tr>
    <td>&nbsp;Adresse : <i>15 RUE DES TROIS EPIS</i></td>
    <td>SIRET : <i>34093842600019</i></td>
  </tr>
  <tr>
    <td>&nbsp;Commune : <i>92200, Neuilly-sur-Seine</i></td>
    <td></td>
  </tr>
  <tr>
    <td>&nbsp;Tel / Fax : <i>01.76.77.33.61 / 01.76.77.33.01</i></td>
    <td></td>
  </tr>
  <tr>
    <td>&nbsp;Email : <i>vlaurent@actualys.com</i></td>
    <td></td>
  </tr>
</table>
</td></tr></table>
<br />
<?php echo h2("Revendication") ?>
<?php $th_style="font-weight: bold; border: 1px solid black;" ?>
<?php $td_style_libelle="border: 1px solid black; text-align: left;" ?>
<?php $td_style_value="border: 1px solid black; text-align: right ;" ?>
<table border="1" cellspacing=0 cellpadding=0 style="text-align: right; border: 1px solid black;">
    <tr>
        <th style="<?php echo $th_style ?> text-align: left; width: 357px">&nbsp;Appellation</th>
        <th style="<?php echo $th_style ?> text-align: center; width: 140px">Superficie</th>
        <th style="<?php echo $th_style ?> text-align: center; width: 140px">Volume</th>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace Pinot noir rosé</td>
        <td style="<?php echo $td_style_value ?>">123.52&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        <td style="<?php echo $td_style_value ?>">100.56&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace Pinot noir rouge</td>
        <td style="<?php echo $td_style_value ?>">123.52&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        <td style="<?php echo $td_style_value ?>">100.56&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace Communale blanc</td>
        <td style="<?php echo $td_style_value ?>">123.52&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        <td style="<?php echo $td_style_value ?>">100.56&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace Communale rouge</td>
        <td style="<?php echo $td_style_value ?>">123.52&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        <td style="<?php echo $td_style_value ?>">100.56&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace Lieu-dit blanc</td>
        <td style="<?php echo $td_style_value ?>">123.52&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        <td style="<?php echo $td_style_value ?>">100.56&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace Lieu-dit rouge</td>
        <td style="<?php echo $td_style_value ?>">123.52&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        <td style="<?php echo $td_style_value ?>">100.56&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace Grands Crus</td>
        <td style="<?php echo $td_style_value ?>">123.52&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        <td style="<?php echo $td_style_value ?>">100.56&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace Crémant</td>
        <td style="<?php echo $td_style_value ?>">123.52&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        <td style="<?php echo $td_style_value ?>">100.56&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
</table>
<br />
<?php echo h2("Dégustation conseil") ?>
<table border="1" cellspacing=0 cellpadding=0 style="text-align: right; border: 1px solid black;">
    <tr>
        <td style="<?php echo $th_style ?> text-align: left; width: 436px"><br />&nbsp;Prélèvement</td>
        <td style="<?php echo $th_style ?> text-align: center; width: 200px">Date à partir de laquelle <br /><small>le vin est prêt à être dégustée</small></td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace</td>
        <td style="<?php echo $td_style_value ?>">Sem. du 1er Janvier 2014</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?> ">&nbsp;Demande de prélévement volontaire des VT / SGN</td>
        <td style="<?php echo $td_style_value ?>">Mars 2014</td>
    </tr>
</table>
<small>&nbsp;</small>
<div><span style="background-color: grey; color: white; font-weight: bold;">Lieu de prélévement</span></div>
<table style="border: 1px solid grey;"><tr><td>
<table border="0">
  <tr>
    <td style="width: 420px;">&nbsp;Nom du responsable : <i>GAEC Actualys Jean</i></td>
    <td>&nbsp;Tel : <i>01.76.77.33.61</i></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;Adresse : <i>15 RUE DES TROIS EPIS, 92200, Neuilly-sur-Seine</i></td>
  </tr>
</table>
</td></tr></table>
<br />
<?php echo h2("Contrôle externe") ?>
<table border="1" cellspacing=0 cellpadding=0 style="text-align: right; border: 1px solid black;">
    <tr>
        <td style="<?php echo $th_style ?> text-align: left; width: 436px"><br />&nbsp;Prélèvement</td>
        <td style="<?php echo $th_style ?> text-align: center; width: 200px">Date à partir de laquelle <br /><small>le vin est prêt à être dégustée</small></td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>">&nbsp;AOC Alsace</td>
        <td style="<?php echo $td_style_value ?>">Sem. du 1er Janvier 2014</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?> ">&nbsp;AOC Alsace Grands Crus</td>
        <td style="<?php echo $td_style_value ?>">Sem. du 1er Janvier 2014</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?> ">&nbsp;Mentions VT/SGN - <b>5 lots</b> (Toutes AOC)</td>
        <td style="<?php echo $td_style_value ?>">Sem. du 1er Février 2015</td>
    </tr>
</table>
<small>&nbsp;</small>
<div><span style="background-color: grey; color: white; font-weight: bold;">Lieu de prélévement</span></div>
<table style="border: 1px solid grey;"><tr><td>
<table border="0">
  <tr>
    <td style="width: 420px;">&nbsp;Nom du responsable : <i>GAEC Actualys Jean</i></td>
    <td>&nbsp;Tel : <i>01.76.77.33.61</i></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;Adresse : <i>15 RUE DES TROIS EPIS, 92200, Neuilly-sur-Seine</i></td>
  </tr>
</table>
</td></tr></table>
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<?php echo h2("Pièces à joindre") ?>
<table style="border: 1px solid grey;"><tr><td>
<table border="0">
  <tr>
    <td>- Une copie de la déclaration de récolte</td>
  </tr>
  <tr>
    <td>- Une copie de la SV12</td>
  </tr>
  <tr>
    <td>- Le carnet de préssoir</td>
  </tr>
</table>
</td></tr></table>
<br />
<p>Signé éléctroniquement <i>via l'application de télédéclaration le 08/06/2014</i></p>