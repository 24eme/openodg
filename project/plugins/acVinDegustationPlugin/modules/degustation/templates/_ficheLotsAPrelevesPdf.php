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
    foreach($lots as $key_lots => $lotsDossier):
        $key_etablissement = explode('/', $key_lots)[2];
        $etablissement = $etablissements[$key_etablissement];
        foreach ($lotsDossier as $numDossier => $lots) :
            foreach ($lots as $num => $lot_indiv) {
                $display[$lot_indiv->declarant_identifiant.'/'.$lot_indiv->adresse_logement][] = $lot_indiv;
            }
        endforeach;
    endforeach;
    ?>

    <?php
        foreach ($display as $adresse => $lots) :
        $lot = $lots[0]->getRawValue();
        $dossiers = [];
        foreach ($lots as $num => $lot) {
            $dossiers[] = $lot->numero_dossier;
        }
    ?>
    <?php if ($ligne % 15 == 0 ) : $table_header = true; ?>
      </table>
      <br pagebreak="true" />
    <?php endif;?>
    <?php if ($table_header): $table_header = false; ?>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <tr style="line-height:20px;">
          <th class="topempty bg-white"style="width:20%;"><?php echo tdStart() ?><strong>Raison sociale</strong></th>
          <th class="topempty bg-white"style="width:30%;"><?php echo tdStart() ?><strong>Lieu de prélèvement /<br/>Coordonnées</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Dossier /<br/> Nombre Lots</strong></th>
          <th class="topempty bg-white"style="width:20%;"><?php echo tdStart() ?><strong>Laboratoire /<br/> Commentaire</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Date /<br/> Heure</strong></th>
        </tr>
    <?php endif;?>
         <tr style="line-height:17px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $etablissements[$lot->declarant_identifiant]->raison_sociale; ?></small></strong></td>
           <td>
             <small><?php
              if($lot->hasLogement()):
                if ($lot->getLogementNom() != $etablissements[$lot->declarant_identifiant]->raison_sociale) {
                    echo substrUtf8($lot->getLogementNom(), 0, 32);
                }?><br/>
                <?php echo substrUtf8($lot->getLogementAdresse(), 0, 32).'<br/>'.substrUtf8($lot->getLogementCodePostal().' '.$lot->getLogementCommune(), 0, 32).'<br/>'; ?>
              <?php else: ?>
                <?php echo  '<br/>'.$etablissements[$lot->declarant_identifiant]->adresse.'<br/>'.$etablissements[$lot->declarant_identifiant]->code_postal.' '.$etablissements[$lot->declarant_identifiant]->commune.'<br/>'; ?>
              <?php endif; ?>
             <?php echo ($etablissements[$lot->declarant_identifiant]->telephone_bureau) ? $etablissements[$lot->declarant_identifiant]->telephone_bureau : '' ?>
             <?php echo ($etablissements[$lot->declarant_identifiant]->telephone_bureau && $etablissements[$lot->declarant_identifiant]->telephone_mobile) ? ' / ' : ''; ?>
             <?php echo ($etablissements[$lot->declarant_identifiant]->telephone_mobile) ? $etablissements[$lot->declarant_identifiant]->telephone_mobile : '' ?>
            </small>
          </td>
          <td><?php echo tdStart() ?>
            <?php foreach (array_unique($dossiers) as $num) : echo $num.' '; endforeach; ?><br/>
            <small>
            <?php
                echo count($lots)." lot";
                echo (count($lots)>1)?'s':'';
            ?>
          </small>
          </td>
          <td><small><br/><?php echo $etablissements[$lot->declarant_identifiant]->getLaboLibelle(); ?></small></td>
          <td><br/><?php echo $lot->getPrelevementFormat() ?></td>
         </tr>
         <?php $ligne++; ?>
     <?php endforeach; ?>
      </table>
