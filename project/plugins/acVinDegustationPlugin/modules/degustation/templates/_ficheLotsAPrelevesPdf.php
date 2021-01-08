<?php use_helper('TemplatingPDF'); ?>
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
          <td style="width:20%;"></td>
          <td style="width: 50%">
            <div border="1px" style="border-style: solid; background-color: #E0E0E0;">
              <p style="margin: 2em; padding-left: 2em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fiche de tournée (Liste des lots à prélever)</p>
            </div>
          </td>
          <td style="width:20%;"></td>
        </tr>
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
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <tr style="line-height:20px;">
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Raison sociale</strong></th>
          <th class="topempty bg-white"style="width:20%;"><?php echo tdStart() ?><strong>Adresse prélèvement</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Tel / Port / Fix</strong></th>
          <th class="topempty bg-white"style="width:12%;"><?php echo tdStart() ?><strong>N° Dos / Nb Lots</strong></th>
          <th class="topempty bg-white"style="width:16%;"><?php echo tdStart() ?><strong>Laboratoire</strong></th>
          <th class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>Date / Heure</strong></th>
          <th class="topempty bg-white"style="width:12%;"><?php echo tdStart() ?><strong>Date<br/> commission</strong></th>
        </tr>
        <?php  foreach($etablissements as $numDossier => $etablissement): ?>
         <tr style="line-height:17px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $etablissement->raison_sociale ?></small></strong></td>
           <td><?php echo tdStart() ?>
              <strong><small><?php echo $etablissement->adresse ?></small></strong><br/>
              <small><?php echo $etablissement->code_postal. ' '.$etablissement->commune; ?></small>
           </td>
           <td><?php echo tdStart() ?>
             <small>
             <?php echo ($etablissement->telephone_bureau) ? 'Tel: '.$etablissement->telephone_bureau : '' ?><br/>
             <?php echo ($etablissement->telephone_perso) ? 'Port: '.$etablissement->telephone_perso : '' ?><br/>
             <?php echo ($etablissement->fax) ? 'Fax: '.$etablissement->fax : '' ?>
            </small>
          </td>
          <td><?php echo tdStart() ?>
            <small><?php echo $numero_dossier; ?></small> /
            <small><?php echo count($lots[$numDossier]); ?></small>
          </td>
          <td><?php echo tdStart() ?>
            <small><?php echo $degustation->lieu; ?></small>
          </td>
          <td><?php echo tdStart() ?>

          </td>
          <td><?php echo tdStart() ?>
            <small> <?php $date = explode("-", substr($degustation->date, 0, 10));echo "$date[2]/$date[1]/$date[0]"; ?></small>
          </td>
         </tr>
       <?php endforeach; ?>
      </table>
      <table>
        <tr style="line-height: 25em; height:25em;">
          <td style="width:20%;"></td>
          <td style="width:80%;"><?php echo "Nombre total d'opérateurs : ".count($etablissements)." - Nombre total de lots à Prélever : ".$nbLotTotal; ?></td>
        </tr>
      </table>
    </div>
