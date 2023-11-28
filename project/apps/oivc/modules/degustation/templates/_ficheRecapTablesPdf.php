<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}
th {
  background-color:white;
}

</style>
      <div></div>
      <table>
        <tr>
          <td style="width:25%;">Date : <i><?php $date = date_create($degustation->date); echo $date->format("d/m/Y"); ?></i></td>
          <td style="width:25%;">Heure : <i><?php echo $date->format("H:i"); ?></i></td>
          <td style="width:50%;">Commission: <i><?php echo $degustation->_id; ?></i></td>
        </tr>
        <tr>
            <td style="width:50%"><?php echo tdStart() ?>Lieu : <i><?php echo $degustation->getLieuNom(); ?></i></td>
            <td style="width:50%;"><?php echo tdStart() ?>Campagne: <i><?php echo $degustation->campagne;?></i></td>
        </tr>
      </table>
      <div></div>
      <table>
        <tr style="line-height:20em;">
          <td style="width:12%">Table :</td>
          <td border="1px" style="width:10%; border-style: solid;text-align:right;">
              <?php echo DegustationClient::getNumeroTableStr($numTab); ?> &nbsp;  &nbsp;
          </td>
        </tr>
      </table>

<p>Nous soussignés, déclarons avoir dégusté ce jour <?php echo count($lots) ?> échantillons en AOC <?php echo implode(', ', $degustation->getAppellationsLots()->getRawValue()) ?>, revêtus d'un simple numéro d'ordre.<br />Les conclusions de notre dégustation effectuée en toute impartialité sont mentionnées ci-après.</p>

      <?php  $ligne=12; $table_header = true;
      foreach($lots as $numAnonyme => $lotInfo): ?>
      <?php if($ligne % 26 == 0): $table_header = true; ?>
        </table>
          <br pagebreak="true" />
          <table>
            <tr style="line-height:20em;">
              <td style="width:12%">Table <small>(suite)</small> :</td>
              <td border="1px" style="width:10%; border-style: solid;text-align:right;">
                  <?php echo DegustationClient::getNumeroTableStr($numTab); ?> &nbsp;  &nbsp;
              </td>
            </tr>
          </table>
          <div></div>
      <?php endif; ?>
      <?php if ($table_header): $table_header = false; ?>
          <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
            <tr style="line-height:20px;">
               <th class="topempty bg-white"style="width:10%; "><?php echo tdStart() ?><strong>Anon</strong></th>
               <th class="topempty bg-white" style="width:30%; "><?php echo tdStart() ?><strong>Produit millesime</strong></th>
               <th class="bg-white" colspan="2" style="width:20%;"><?php echo tdStart() ?><strong>Avis</strong></th>
               <th class="topempty bg-white" style="width:40%;"><strong>Motifs (si avis défavorable)</strong></th>
            </tr>
            <tr style="line-height:13px;">
              <th class="empty bg-white"></th>
              <th class="empty bg-white"></th>
              <th class="bg-white" style="width:10%;" ><?php echo tdStart() ?><strong><small>Fav.</small></strong></th>
              <th class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>Def.</small></strong></th>
              <th class="empty bg-white"></th>
            </tr>
      <?php endif; ?>
         <tr style="line-height:15px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $lotInfo->getNumeroAnonymat() ?></small></strong></td>
           <td><?php echo tdStart() ?><small><?php echo substrUtf8(strip_tags(showOnlyProduit($lotInfo, false)), 0, 35);; ?></small><br/><small><?php echo showOnlyCepages($lotInfo, 45);?></small></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?>&nbsp;</td>
         </tr>
         <?php $ligne++; ?>
       <?php endforeach; ?>
      </table>

<p>L'agent habilité, et les membres de la commission signataires certifient l'exactitude du présent procès-verbal.</p>

      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <thead>
          <tr>
            <th style="width:15%" colspan="3">Collège</th>
            <th class="topempty bg-white" style="width:35%">Noms et prénoms des membres</th>
            <th class="topempty bg-white" style="width:25%">Signatures</th>
            <th class="topempty bg-white" style="width:25%">Nom et signature de</th>
          </tr>
          <tr>
            <th style="width:5%">PM</th>
            <th style="width:5%">T</th>
            <th style="width:5%">UP</th>
            <th class="empty bg-white">de la commission</th>
            <th class="empty bg-white"></th>
            <th class="empty bg-white">l'agent habilité</th>
          </tr>
        </thead>
        <tbody>
          <?php $j=0; foreach ($degustation->getDegustateursConfirmesTableOrFreeTable($numTab) as $id_compte => $degustateur): ?>
            <?php if (! $degustateur->exist("numero_table") || $degustateur->numero_table == null): continue; endif; ?>
            <?php $compte = CompteClient::getInstance()->find($id_compte); ?>
              <tr style="line-height:35px;">
                  <td style="width:5%"><br /></td>
                  <td style="width:5%"><br /></td>
                  <td style="width:5%"><br /></td>
                  <td class="text-center" style="width:35%"><?php echo $compte->getNom() ?> <?php echo $compte->getPrenom() ?></td>
                  <td style="width:25%"><br /></td>
                  <?php if ($j==0): ?>
                  <td style="width:25%" rowspan="5"><br /></td>
                  <?php endif; ?>
              </tr>
          <?php $j++; endforeach; ?>
          <?php $t = count($degustation->getDegustateursConfirmesTableOrFreeTable($numTab)); ?>
          <?php for ($i = $t; $i<5; $i++): ?>
              <tr style="line-height:35px;">
                  <td style="width:5%"><br /></td>
                  <td style="width:5%"><br /></td>
                  <td style="width:5%"><br /></td>
                  <td class="text-center" style="width:35%"><br /></td>
                  <td style="width:25%"><br /></td>
                  <?php if ($j==0): ?>
                  <td style="width:25%" rowspan="5"><br /></td>
                  <?php endif; ?>
              </tr>
          <?php $j++; endfor; ?>
        </tbody>
      </table>

<p>PM (Porteur de Mémoire) = vignerons, négociants en activité ou retraités<br />T (Technicien)<br />UP (Usager du Produit) = amateur (membres club oenologie...)</p>

      <div></div>
