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
    <div>
      <table>
        <tr>
          <td style="width:20%;">
          </td>
          <td style="width:30%;">
            <p>Préleveur :</p>
          </td>
          <td style="width:30%">
            <p>Date d'édition : <?php echo $date_edition;?></p>
          </td>
          <td style="width:20%;">
          </td>
        </tr>
      </table>
    </div>
    <div>
      <table>
        <tr style="line-height: 25em; height:25em;">
          <td style="text-align: center"><?php echo "Nombre total d'opérateurs : ".count($etablissements)." - Nombre total de lots à Prélever : ".$nbLotTotal; ?></td>
        </tr>
      </table>
    <?php $ligne = 1; $table_header = true;
    foreach($lots as $adresse => $lotsArchive):
        $etablissement = $etablissements[$adresse];
        $adresseLogement = splitLogementAdresse($adresse);

        $numDossier = null;
        foreach ($lotsArchive as $lot) {
          $numDossier = $lot->numero_archive;
          break;
        }
    ?>
    <?php if ($ligne % 12 == 0 ) : $table_header = true; ?>
      </table>
      <br pagebreak="true" />
      <p>Suite des lots<p/>
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
                if ($adresseLogement['nom'] != $etablissement->raison_sociale) {
                    echo substr($adresseLogement['nom'], 0, 32).'<br/>';
                }
                echo substr($adresseLogement['adresse'], 0, 32); ?><br/>
                <?php echo substr($adresseLogement['code_postal'].' '.$adresseLogement['commune'], 0, 32); ?><br/>
                <?php if ($adresseLogement['nom'] == $etablissement->raison_sociale): ?>
                    <br/>
                <?php endif; ?>
             <?php echo ($etablissement->telephone_bureau) ? $etablissement->telephone_bureau : '' ?>
             <?php echo ($etablissement->telephone_bureau && $etablissement->telephone_mobile) ? ' / ' : ''; ?>
             <?php echo ($etablissement->telephone_mobile) ? $etablissement->telephone_mobile : '' ?>
            </small>
          </td>
          <td><?php echo tdStart() ?>
            N° dossier : <?php echo $numDossier; ?><br/>
            <small>
            <?php $lotTypesNb = $degustation->getNbLotByAdresseLogt($etablissement->identifiant, $adresse); ?>
            <?php foreach ($lotTypesNb as $provenance => $nb) {
                echo $nb." lot";
                echo ($nb>1)?'s':'';
                echo " provenant ";
                switch ($provenance) {
                    case 'DEGU': echo "d'une dégustation";break;
                    case 'DREV': echo "d'une revendication";break;
                    case 'COND': echo "d'un conditionnement";break;
                    case 'TRAN': echo "d'une transaction";break;
                }
            }?>
          </small>
          </td>
          <td><small><br/><?php echo $etablissement->getLaboLibelle(); ?></small></td>
          <td></td>
         </tr>
         <?php $ligne++; ?>
      <?php endforeach; ?>
      </table>
    </div>
