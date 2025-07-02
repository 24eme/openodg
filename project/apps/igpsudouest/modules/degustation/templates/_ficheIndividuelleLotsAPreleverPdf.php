<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<?php use_helper('Float') ?>
<?php use_helper('Text') ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}
th {
  background-color:white;
  font-weight: bold;
}

p, div {
 line-height: 0.5;
}

.border{
  border-style: solid;
}

</style>
<table class="table" cellspacing=0 cellpadding=0 style="border-collapse:collapse;" scope="colgroup">
  <?php echo tdStart() ?>
  <tr>
      <th border="1px" class="border" style="width:50%; text-align: center;"><strong>Opérateur</strong></th>
      <th border="1px" class="border" style="width:50%; text-align: center;"><strong>Lieu entreposage et prélèvement</strong></th>
  </tr>
  <tr>
    <td border="1px" class="border">
        <p><span>&nbsp;&nbsp;<strong>Raison sociale :</strong> <?php echo truncate_text($etablissement->getRawValue()->raison_sociale, 43, '...') ?></span></p>
        <p>
          <span><strong>Adresse :</strong> <?php echo $etablissement->adresse ?></span>
        </p>
        <p>
          <span><strong>Code postal :</strong> <?php echo $etablissement->code_postal ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Ville :</strong> <?php echo $etablissement->commune ?></span>
        </p>
        <p>
          <span><strong>Téléphone :</strong> <?php echo $etablissement->telephone_bureau ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Portable :</strong> <?php echo $etablissement->telephone_mobile ?></span>
        </p>
        <p>
          <span><strong>Siret :</strong> <?php echo $etablissement->siret ?></span>
          <span><?php for($i=strlen($etablissement->siret); $i <= 25; $i++): ?>&nbsp;<?php endfor; ?><strong>N° CVI :</strong> <?php echo $etablissement->cvi ?></span>
          <br />
        </p>
    </td>
    <td border="1px" class="border">
<?php
    $lot = array_values($lots->getRawValue())[0];
?>
        <p>
          <span><strong>Nom :</strong> <?php echo truncate_text($lot->getLogementNom(), 43, '...') ?></span>
        </p>
        <p>
          <span><strong>Adresse :</strong> <?php echo $lot->getLogementAdresse(); ?></span>
        </p>
        <p>
          <span><strong>Code postal : </strong><?php echo $lot->getLogementCodePostal(); ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Ville</strong> : <?php echo $lot->getLogementCommune(); ?></span>
        </p>
        <p>
          <span><strong>Téléphone :</strong> <?php echo $lot->getLogementTelephone() ?> <?php echo ($lot->getLogementPortable()) ? ' / '.$lot->getLogementPortable() : ''; ?></span>
        </p>
        <p>
            <strong>Laboratoire :</strong> <?php echo $etablissement->getLaboLibelle(); ?>
            <br />
        </p>
    </td>
  </tr>
</table>
<br />
<br />
<table class="table" cellspacing=0 cellpadding=0 style="border-collapse:collapse;" scope="colgroup">
  <tr>
    <th border="1px" class="border" style="width: 12%; text-align: center;"><strong>Date</strong></th>
    <th border="1px" class="border" style="width: 12%; text-align: center;"><strong>Heure</strong></th>
    <th border="1px" class="border" style="width: 38%; text-align: center;"><strong>Nom et signature préleveur</strong></th>
    <th border="1px" class="border" style="width: 38%; text-align: center;"><strong>Nom et signature personne présente</strong></th>
  </tr>
  <tr>
    <td border="1px" class="border"><br/><br/></td>
    <td border="1px" class="border"></td>
    <td border="1px" class="border"></td>
    <td border="1px" class="border"></td>
  </tr>
</table>
<br />
<p><strong><?php echo count($lots) ?> lot(s) à prélever</strong> <small>(pour un volume total de <?php echo $volumeLotTotal ?> hl)</small> :</p>
  <?php $i = 10; $table_header = true; foreach($lots as $key => $lot): ?>
   <?php if($i % 20 == 0 ): $table_header = true; ?>
    </table>
     <br pagebreak="true" />
     <p><strong>Suite des lots :</strong><p/>
     <br/>
     <?php endif; ?>
    <?php if ($table_header): $table_header = false; ?>
     <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
       <tr style="line-height:20px;">
         <th style="width: 8%"><?php echo tdStart() ?><small>N° Dossier/Lot</small></th>
         <th style="width: 33%"><?php echo tdStart() ?><small>Produit/Cépage/Millésime</small></th>
         <th style="width: 5%"><?php echo tdStart() ?><small>Volume<br/>(hl)</small></th>
         <th style="width: 6%"><?php echo tdStart() ?><small>Enrichi<br/></small></th>
         <th style="width:8%"><?php echo tdStart() ?><small>N°Lot<br/>Opérateur</small></th>
         <th style="width: 10%"><?php echo tdStart() ?><small>Passage (spécificité)</small></th>
         <th style="width: 13%"><?php echo tdStart() ?><small>Destination</small></th>
         <th style="width: 17%"><?php echo tdStart() ?><small>Obs préleveur <br/>Obs opérateurs</small></th>
       </tr>
   <?php endif;?>
   <tr style="line-height:17px;">
     <td><?php echo tdStart() ?><small><?php echo $lot->numero_dossier.' / '.$lot->numero_archive ?></small></td>
     <td style="text-align:left;"><?php echo tdStart() ?><span style="font-size: 11px;"><?php echo showProduitCepagesLot($lot, true, 'span'); ?></span></td>
    <td><?php echo tdStart() ?>
      <small><?php echoFloat($lot->volume); ?></small>
    </td>
    <td><?php echo tdStart() ?><span class="zap">o</span>
    </td>
    <td><?php echo tdStart() ?>
      <small><?php echo $lot->numero_logement_operateur ?></small>
    </td>
    <td><?php echo tdStart() ?>
      <small><?php echo $lot->isSecondPassage() ? $lot->getTextPassage(false) : $lot->getTextPassage(false)." $lot->specificite"; ?></small>
    </td>
    <td><?php echo tdStart() ?>
      <small>
        <?php echo $lot->getDestinationShort() ?>
      </small>
    </td>
    <td><?php echo tdStart() ?>
    </td>
   </tr>
   <?php $i++; ?>
  <?php endforeach; ?>
  </table>
