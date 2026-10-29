<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot'); ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}
th {
  background-color:white;
}

</style>
    <?php $ligne = 1; $table_header = true;
    foreach($lots as $logement => $lots_du_logement):
        $etablissement = $etablissements[$logement];
        foreach ($lots_du_logement as $lot) :
    ?>
    <?php if ($ligne % 10 == 0 ) : $table_header = true; ?>
      </table>
      <br pagebreak="true" />
    <?php endif;?>
    <?php if ($table_header): $table_header = false; ?>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <tr style="line-height:20px;">
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Raison sociale</strong></th>
          <th class="topempty bg-white"style="width:20%;"><?php echo tdStart() ?><strong>Adresse</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Produit</strong></th>
          <th class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>Cuve</strong></th>
          <th class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>N° Lot</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Observation</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Date & Signature</strong></th>
        </tr>
    <?php endif;?>
         <tr style="line-height:17px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $etablissement->raison_sociale; ?></small></strong></td>
           <td>
             <small><?php
              if($lot->hasLogement()):
                if ($lot->getLogementNom() != $etablissement->raison_sociale) {
                    echo substrUtf8($lot->getLogementNom(), 0, 32).'<br/>';
                }?>
                <?php echo substrUtf8($lot->getLogementAdresse(), 0, 32).'<br/>'.substrUtf8($lot->getLogementCodePostal().' '.$lot->getLogementCommune(), 0, 32).'<br/>'; ?>
              <?php else: ?>
                <?php echo  $etablissement->adresse.'<br/>'.$etablissement->code_postal.' '.$etablissement->commune.'<br/>'; ?>
              <?php endif; ?>
             <?php echo ($etablissement->telephone_bureau) ? $etablissement->telephone_bureau : '' ?>
             <?php echo ($etablissement->telephone_bureau && $etablissement->telephone_mobile) ? ' / ' : ''; ?>
             <?php echo ($etablissement->telephone_mobile) ? $etablissement->telephone_mobile : '' ?></small>
          </td>
          <td><?php echo tdStart() ?>
              <small>
              <?php echo $lot->getProduitLibelle(); ?> <?php echo $lot->millesime; ?><br/>
              <?php echo $lot->volume; ?> hl</small>
          </td>
          <td><small><?php echo $lot->getNumeroLogementOperateur() ?></small></td>
          <td><small><?php echo $lot->getNumeroDossier() ?><br/>/<br/><?php echo $lot->getNumeroArchive() ?></small></td>
          <td></td><td></td>
         </tr>
         <?php $ligne++; ?>
      <?php endforeach; ?>
   <?php endforeach; ?>
      </table>
      <table>
        <tr style="line-height: 25em; height:25em;">
          <td></td>
        </tr>
      </table>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
      <?php $nb_cols = 5; ?>
      <tr style="line-height:20px;">
      <?php for ($i=0; $i < $nb_cols; $i++) : ?>
            <td class="topempty bg-white"style="width:<?php echo (100/5); ?>%;"><?php echo tdStart() ?><strong><small>Nom du préleveur</small></strong></td>
        <?php endfor; ?>
        </tr>
        <tr style="line-height:20px;">
        <?php for ($i=0; $i < $nb_cols; $i++) : ?>
            <td>&nbsp;<br />&nbsp;</td>
        <?php endfor; ?>
        </tr>
        <tr style="line-height:20px;">
        <?php for ($i=0; $i < $nb_cols; $i++) : ?>
            <td class="topempty bg-white"><?php echo tdStart() ?><strong><small>Date et signature</small></strong></td>
        <?php endfor; ?>
        </tr>
        <tr style="line-height:20px;">
        <?php for ($i=0; $i < $nb_cols; $i++) : ?>
            <td>&nbsp;<br />&nbsp;<br />&nbsp;</td>
        <?php endfor; ?>
        </tr>
        </table>
