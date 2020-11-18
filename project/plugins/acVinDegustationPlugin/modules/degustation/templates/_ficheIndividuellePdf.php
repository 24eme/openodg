<?php use_helper('TemplatingPDF'); ?>
<style>
</style>
    <table border="" class="table" cellspacing=0 cellpadding=0 style="text-align: left;">

      <table>
        <tr>
          <td><?php echo tdStart() ?><br>

              <strong>Date : <?php echo $degustation->date  ?></strong><br>
              <strong>Heure : </strong><br>
              <strong>Commission: 19-14</strong><br>
          </td>
          <td><?php echo tdStart() ?><br>
              <strong>Campagne : <?php echo $degustation->campagne ?></strong><br>
              <strong>Millesime :<?php echo $lots[1]->lot->millesime; ?></strong><br>
          </td>
          <td><?php echo tdStart() ?><br>
            <strong>Lieu: <?php echo $degustation->lieu; ?></strong>
          </td>
        </tr>
      </table>
      <p> <strong> Nom : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
          <strong>Prénom : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
          <strong>Signature : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
          <strong>Jury n° : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
      </p>
    </table>

    <table border="" class="" cellspacing=0 cellpadding=0 style="text-align: center;">
      <tr>
         <td>N°Ano</td>
         <td>Couleur</td>
         <td>IGP</td>
         <td>Cépage</td>
         <td>
           <table border="" class="" cellspacing=0 cellpadding=0 style="text-align: center;">
             <tr>
               <td colspan="4">Notation</td>
             </tr>
             <tr>
               <td>Visuel<br>/12</td>
               <td>Oifactif <br>/12</td>
               <td>Gustatif <br>/24</td>
               <td>NOTE TOTALE<br> /48</td>
             </tr>
           </table>
         </td>
         <td>
           <table border="" class="" cellspacing=0 cellpadding=0 style="text-align: center;">
             <tr>
               <td colspan="2">Avis</td>
             </tr>
             <tr>
               <td>C</td>
               <td>NC</td>
             </tr>
           </table>
         </td>
         <td>
           <table border="" class="" cellspacing=0 cellpadding=0 style="text-align: center;">
             <tr>
               <td colspan="2">Typicité <br>cépage</td>
             </tr>
             <tr>
               <td>C</td>
               <td>NC</td>
             </tr>
           </table>
         </td>
         <td>Motifs</td>
      </tr>
    <?php $i=1?>
    <?php foreach($lots as $lotInfo): ?>
      <tr style="line-height:7px;">
        <td >
          <?php echo tdStart() ?>&nbsp;<strong><?php echo $i ?></strong>
        </td>

        <td >
          <?php echo tdStart() ?>&nbsp;<strong><?php echo $lotInfo->couleur;  ?></strong>
        </td>

        <td>
          <?php echo tdStart() ?>&nbsp;<?php echo $lotInfo->lot->millesime;  ?>
        </td>

        <td>
          <?php echo tdStart() ?>&nbsp;<?php echo $lotInfo->lot->millesime;  ?>
        </td>
        <td>
          <?php echo tdStart() ?>&nbsp;<?php echo $lotInfo->lot->millesime;  ?>
        </td>
        <td>
          <?php echo tdStart() ?>&nbsp;<?php echo $lotInfo->lot->millesime;  ?>
        </td>

        <td>
          <?php echo tdStart() ?>&nbsp;<?php echo $lotInfo->lot->millesime;  ?>
        </td>
        <td>
          <?php echo tdStart() ?>&nbsp;<?php echo $lotInfo->lot->millesime;  ?>
        </td>
      </tr>

      <?php $i=$i+1 ?>
    <?php endforeach; ?>
  </table>
