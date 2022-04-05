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
      <table>
        <tr>
          <td style="width:20%;"></td>
          <td style="width:30%;">Préleveur :</td>
          <td style="width:30%">Date d'édition : <?php echo $date_edition;?></td>
          <td style="width:20%;"></td>
        </tr>
      </table>
      <table>
        <tr style="line-height: 25em; height:25em;">
          <td style="text-align: center"><?php echo "Nombre total d'opérateurs : ".count($etablissements)." - Nombre total de lots à Prélever : ".$nbLotTotal; ?></td>
        </tr>
      </table>
    <?php $ligne = 1; $table_header = true;
    foreach($lots as $key_lots => $lotsDossier):
        $key_etablissement = explode('/', $key_lots)[1];
        $etablissement = $etablissements[$key_etablissement];
        foreach ($lotsDossier as $numDossier => $lots) :
            $lot = $lots[0]->getRawValue();
            $adresse = $lot->adresse_logement;
            $adresseLogement = splitLogementAdresse($lot->adresse_logement);
    ?>
    <?php if ($ligne % 15 == 0 ) : $table_header = true; ?>
      </table>
      <br pagebreak="true" />
    <?php endif;?>
    <?php if ($table_header): $table_header = false; ?>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <tr style="line-height:20px;">
          <th class="topempty bg-white"style="width:20%;"><?php echo tdStart() ?><strong>Raison sociale</strong></th>
          <th class="topempty bg-white"style="width:30%;"><?php echo tdStart() ?><strong>Coordonnées</strong></th>
          <th class="topempty bg-white"style="width:25%;"><?php echo tdStart() ?><strong>Dossier /<br/> Nombre Lots</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Laboratoire</strong></th>
          <th class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>Date /<br/> Heure</strong></th>
        </tr>
    <?php endif;?>
         <tr style="line-height:17px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $etablissement->raison_sociale; ?></small></strong></td>
           <td>
             <small><?php
              if($adresseLogement):
                if ($adresseLogement['nom'] != $etablissement->raison_sociale) {
                    echo substrUtf8($adresseLogement['nom'], 0, 32).'<br/>';
                }?>
                <?php echo substrUtf8($adresseLogement['adresse'], 0, 32).'<br/>'.substrUtf8($adresseLogement['code_postal'].' '.$adresseLogement['commune'], 0, 32).'<br/>'; ?>
              <?php else: ?>
                <?php echo  $etablissement->adresse.'<br/>'.$etablissement->code_postal.' '.$etablissement->commune.'<br/>'; ?>
              <?php endif; ?>
             <?php echo ($etablissement->telephone_bureau) ? $etablissement->telephone_bureau : '' ?>
             <?php echo ($etablissement->telephone_bureau && $etablissement->telephone_mobile) ? ' / ' : ''; ?>
             <?php echo ($etablissement->telephone_mobile) ? $etablissement->telephone_mobile : '' ?>
            </small>
          </td>
          <td><?php echo tdStart() ?>
            <?php echo $numDossier; ?><br/>
            <small>
            <?php
                echo count($lots)." lot";
                echo (count($lots)>1)?'s':'';
            ?>
          </small>
          </td>
          <td><small><br/><?php echo $etablissement->getLaboLibelle(); ?></small></td>
          <td></td>
         </tr>
         <?php $ligne++; ?>
      <?php endforeach; ?>
   <?php endforeach; ?>
      </table>
