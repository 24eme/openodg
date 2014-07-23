<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper("Date"); ?>

<span style="background-color: #f3c3d3; color: #c75268; font-weight: bold;">&nbsp;Exploitation&nbsp;</span><br/>
<table style="border: 1px solid #f3c3d3;"><tr><td>
<table border="0">
    <tr>
        <td style="width: 420px;">&nbsp;Nom : <i><?php echo $drev->declarant->raison_sociale ?></i></td>
        <td>N° CVI : <i><?php echo $drev->declarant->cvi ?></i></td>
    </tr>
    <tr>
        <td>&nbsp;Adresse : <i><?php echo $drev->declarant->adresse ?></i></td>
        <td>SIRET : <i><?php echo $drev->declarant->siret ?></i></td>
    </tr>
    <tr>
        <td>&nbsp;Commune : <i><?php echo $drev->declarant->code_postal ?>, <?php echo $drev->declarant->commune ?></i></td>
        <td></td>
    </tr>
    <tr>
        <td>&nbsp;Tel / Fax : <i><?php echo $drev->declarant->telephone ?> / <?php echo $drev->declarant->fax ?></i></td>
        <td></td>
    </tr>
    <tr>
        <td>&nbsp;Email : <i><?php echo $drev->declarant->email ?></i></td>
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
    <?php foreach($drev->declaration->getProduits(true) as $produit): ?>
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
    <?php foreach($drev->getPrelevementsByDate(DRev::CUVE) as $prelevement): ?>
    <tr>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>&nbsp;<?php echo $prelevement->libelle_produit ?></td>
        <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>&nbsp;<?php echo format_date($prelevement->date, "D", "fr_FR") ?></td>
        <td style="<?php echo $td_style_value ?>"><?php echo $td_start ?><?php echo $prelevement->total_lots ?>&nbsp;<small>lot (s)</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
    <?php endforeach; ?>
</table>
<small>&nbsp;</small>
<div><span style="background-color: #f3c3d3; color: #c75268; font-weight: bold;">&nbsp;Lieu de prélévement&nbsp;</span></div>
<table style="border: 1px solid #f3c3d3;"><tr><td>
<table border="0">
    <tr>
        <td style="width: 420px;">&nbsp;Nom du responsable : <i><?php echo $drev->declarant->raison_sociale ?></i></td>
        <td>&nbsp;Tel : <i><?php echo $drev->declarant->telephone ?></i></td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;Adresse : <i><?php echo $drev->declarant->adresse ?>, <?php echo $drev->declarant->code_postal ?>, <?php echo $drev->declarant->commune ?></i></td>
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
    <?php if(count($drev->getPrelevementsByDate(DRev::BOUTEILLE)) > 0): ?>
        <?php foreach($drev->getPrelevementsByDate(DRev::BOUTEILLE) as $prelevement): ?>
        <tr>
            <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?>&nbsp;<?php echo $prelevement->libelle_produit ?></td>
            <td style="<?php echo $td_style_libelle ?>"><?php echo $td_start ?><?php echo format_date($prelevement->date, "D", "fr_FR") ?></td>
            <td style="<?php echo $td_style_value ?>"><?php echo $td_start ?><?php echo $prelevement->total_lots ?>&nbsp;<small>lot (s)</small>&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
          <td colspan="3" style="<?php echo $td_style_libelle ?> text-align: center;"><?php echo $td_start ?>&nbsp;<i>Aucun prélévement prévu</i></td>
        </tr>
    <?php endif; ?>
</table>
<small>&nbsp;</small>
<div><span style="background-color: #f3c3d3; color: #c75268; font-weight: bold;">&nbsp;Lieu de prélèvement&nbsp;</span></div>
<table style="border: 1px solid #f3c3d3;"><tr><td>
  <table border="0">
      <tr>
          <td style="width: 420px;">&nbsp;Nom du responsable : <i><?php echo $drev->declarant->raison_sociale ?></i></td>
          <td>&nbsp;Tel : <i><?php echo $drev->declarant->telephone ?></i></td>
      </tr>
      <tr>
          <td colspan="2">&nbsp;Adresse : <i><?php echo $drev->declarant->adresse ?>, <?php echo $drev->declarant->code_postal ?>, <?php echo $drev->declarant->commune ?></i></td>
      </tr>
  </table>
</td></tr></table>
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