<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>

<span style="background-color: #f3c3d3; color: #c75268; font-weight: bold;">&nbsp;Exploitation&nbsp;</span><br/>
<table style="border: 1px solid #f3c3d3;"><tr><td>
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
<?php $th_style="font-weight: normal; border: 1px solid #c75268; background-color: #f7dce5; color: #c75268;" ?>
<?php $td_style_libelle="border: 1px solid #c75268; text-align: left; height:22px;" ?>
<?php $td_style_value="border: 1px solid #c75268; text-align: right; height:22px;" ?>
<?php $td_start="<small style=\"font-size: 2pt;\"><br /></small>" ?>
<table border="1" cellspacing=0 cellpadding=0 style="text-align: right; border: 1px solid #c75268;">
    <tr>
        <th style="<?php echo $th_style ?> text-align: left; width: 357px">&nbsp;Appellation</th>
        <th style="<?php echo $th_style ?> text-align: center; width: 140px">Superficie</th>
        <th style="<?php echo $th_style ?> text-align: center; width: 140px">Volume</th>
    </tr>
    <?php foreach($drev->declaration->getProduits() as $produit): ?>
    <tr>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>&nbsp;<?php echo $produit->getLibelleComplet() ?></td>
        <td style="<?php echo $td_style_value ?>"><?php echo $td_start ?><?php echo sprintFloatFr($produit->total_superficie) ?>&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        <td style="<?php echo $td_style_value ?>"><?php echo $td_start ?><?php echo sprintFloatFr($produit->volume_revendique) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
  <?php  endforeach; ?>
</table>
<br />
<br />
<table cellspacing=0 cellpadding=0 style="text-align: right;">
<tr><td style="border-bottom: 1px solid #c75268;"><span style="text-align: left; font-size: 12pt; color: #c75268">Dégustation conseil</span></td></tr>
</table>
<br />
<?php echo h2("Prélèvement") ?>
<table border="1" cellspacing=0 cellpadding=0 style="text-align: right; border: 1px solid #c75268;">
    <tr>
        <td style="<?php echo $th_style ?> text-align: left; width: 258px">&nbsp;Produit</td>
        <td style="<?php echo $th_style ?> text-align: left; width: 260px">&nbsp;Date <small>(à partir de laquelle le vin est prêt à être dégusté)</small>&nbsp;</td>
        <td style="<?php echo $th_style ?> text-align: center; width: 121px">Lots</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>&nbsp;AOC Alsace</td>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>&nbsp;Semaine du 1er Janvier 2014</td>
        <td style="<?php echo $td_style_value ?>"><?php echo $td_start ?>2&nbsp;<small>lot (s)</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?> "><?php echo $td_start ?>&nbsp;Mentions VT / SGN (Volontaire)</td>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>&nbsp;Mars 2014</td>
        <td style="<?php echo $td_style_value ?>"><?php echo $td_start ?>3&nbsp;<small>lot (s)</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
</table>
<small>&nbsp;</small>
<div><span style="background-color: #f3c3d3; color: #c75268; font-weight: bold;">&nbsp;Lieu de prélévement&nbsp;</span></div>
<table style="border: 1px solid #f3c3d3;"><tr><td>
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
<table cellspacing=0 cellpadding=0 style="text-align: right;">
<tr><td style="border-bottom: 1px solid #c75268;"><span style="text-align: left; font-size: 12pt; color: #c75268">Contrôle externe</span></td></tr>
</table>
<br />
<?php echo h2("Prélèvement") ?>
<table border="1" cellspacing=0 cellpadding=0 style="text-align: right; border: 1px solid #c75268;">
    <tr>
        <td style="<?php echo $th_style ?> text-align: left; width: 258px">&nbsp;Produit</td>
        <td style="<?php echo $th_style ?> text-align: left; width: 260px">&nbsp;Date <small>(à partir de laquelle le vin est prêt à être dégusté)</small>&nbsp;</td>
        <td style="<?php echo $th_style ?> text-align: center; width: 121px">Lots</td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>&nbsp;AOC Alsace</td>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>Semaine du 1er Janvier 2014</td>
        <td style="<?php echo $td_style_value ?>"><?php echo $td_start ?></td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>&nbsp;AOC Alsace Grands Crus</td>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>Semaine du 1er Janvier 2014</td>
        <td style="<?php echo $td_style_value ?>"><?php echo $td_start ?></td>
    </tr>
    <tr>
        <td style="<?php echo $td_style_libelle ?> "><?php echo $td_start ?>&nbsp;Mentions VT / SGN (Toutes AOC)</td>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>Semaine du 1er Février 2015</td>
        <td style="<?php echo $td_style_value ?>"><?php echo $td_start ?>5&nbsp;<small>lot (s)</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
</table>
<small>&nbsp;</small>
<div><span style="background-color: #f3c3d3; color: #c75268; font-weight: bold;">&nbsp;Lieu de prélèvement&nbsp;</span></div>
<table style="border: 1px solid #f3c3d3;"><tr><td>
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
<?php echo h2("Pièces à joindre") ?>
<table style="border: 1px solid #c75268;"><tr><td>
<table border="0">
  <tr>
    <td>- Une copie de la déclaration de Récolte</td>
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