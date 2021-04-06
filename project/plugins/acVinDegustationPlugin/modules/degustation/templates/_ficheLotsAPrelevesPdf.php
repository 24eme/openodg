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
        <?php echo tdStart() ?>
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
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <tr style="line-height:20px;">
          <th class="topempty bg-white"style="width:20%;"><?php echo tdStart() ?><strong>Raison sociale</strong></th>
          <th class="topempty bg-white"style="width:30%;"><?php echo tdStart() ?><strong>Coordonnées</strong></th>
          <th class="topempty bg-white"style="width:25%;"><?php echo tdStart() ?><strong>Dossier /<br/> Nombre Lots</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Laboratoire</strong></th>
          <th class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>Date /<br/> Heure</strong></th>
        </tr>
        <?php $ligne = 0; $page = 1;
        $currentAdresse = uniqid();
    foreach($lots as $numDossier => $lotsArchive): ?>
    <?php $etablissement = $etablissements[$numDossier]; ?>
    <?php foreach($lotsArchive as $archive => $lot):
            if($lot->adresse_logement == $currentAdresse){
                continue;
            }
            $adresseLogement = splitLogementAdresse($lot->adresse_logement);
            $currentAdresse = $lot->adresse_logement;
    ?>
    <?php if(($ligne == 10 && $page == 1) || ($ligne == 12 && $page > 1)): //display 14 Lots on the first page and below 17 Lots all others pages?>
      </table>
      <br pagebreak="true" />
      <p>Suite des lots<p/>
      <?php $ligne = 0; $page++ ?>

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
             <small><?php echo $adresseLogement['adresse']; ?></small><br/>
             <small><?php echo $adresseLogement['code_postal']; ?> <?php echo $adresseLogement['commune']; ?></small><br/>
             <small>
             <?php echo ($etablissement->telephone_bureau) ? $etablissement->telephone_bureau : '' ?>
             <?php echo ($etablissement->telephone_bureau && $etablissement->telephone_mobile) ? ' / ' : ''; ?>
             <?php echo ($etablissement->telephone_mobile) ? $etablissement->telephone_mobile : '' ?>
            </small>

           </td>
          <td><?php echo tdStart() ?>
            <?php $lotTypesNb = $degustation->getNbLotByTypeForNumDossier($numDossier, $currentAdresse); ?>
            N° dossier : <?php echo $numDossier; ?><br/>
            <small>
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
     <?php endforeach; ?>
      </table>
    </div>
