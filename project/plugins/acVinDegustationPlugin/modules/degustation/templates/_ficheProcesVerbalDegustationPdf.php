<?php use_helper('TemplatingPDF'); ?>
<style>
<?php echo style(); ?>

th {
  background-color:white;
}

</style>
  <table>
    <tr>
      <td style="width:100%;">
        <p><small>
          <span>Code Commission : <?= $degustation->_id ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;Campagne : <?php echo $degustation->campagne .'/'.($degustation->campagne+1); ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;Millésime : <?php echo $degustation->campagne; ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;Date : <?php $date = explode("-", substr($degustation->date, 0, 10));echo "$date[2]/$date[1]/$date[0]"; ?></span>
          <span>&nbsp;&nbsp;Heure : <?php echo substr($degustation->date, -5); ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;Lieu : <?php echo $degustation->lieu; ?> </span>
        </small>
        </p>

      </td>
    </tr>
  </table>

  <p>TABLEAU DE SYNTHÈSE GLOBAL DES LOTS DE VIN IGP PRÉSENTÉS À LA COMMISSION :</p>
  <table>
    <tr>
      <td style="width:2%"></td>
      <td style="width:60%">
        <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
          <thead>
            <tr>
              <th rowspan="2" style="width:35%"></th>
              <th rowspan="2" style="width:15%"><small>Synthèse</small></th>
              <th colspan="2" style="width:10%"><small>Résultat</small></th>
            </tr>
            <tr>
              <th><small>C</small></th>
              <th><small>NC</small></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th><small>Nombre de lots</small></th>
              <td><small><?php echo $nbLotTotal ?></small></td>
              <td><small><?php echo $degustation->getNbLotsConformes() ?></small></td>
              <td><small><?php echo $degustation->getNbLotsNonConformes() ?></small></td>
            </tr>
            <tr>
              <th><small>Volumes total (hl)</small></th>
              <?php $volumeNC = $degustation->getVolumeLotsConformesOrNot(); $volumeC = $degustation->getVolumeLotsConformesOrNot(true) ?>
              <td><small><?php echo $volumeNC + $volumeC ?></small></td>
              <td><small><?php echo $volumeC ?></small></td>
              <td><small><?php echo $volumeNC ?></small></td>
            </tr>
            <tr>
              <th><small>Nombre d'opérateurs</small></th>
              <td><small><?php echo count($etablissements) ?></small></td>
              <td><small><?php echo count($degustation->getEtablissementLotsConformesOrNot(true)) ?></small></td>
              <td><small><?php echo count($degustation->getEtablissementLotsConformesOrNot()) ?></small></td>
            </tr>
          </tbody>
        </table>
      </td>
      <td style="width:35%">
        <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
          <tr style="width:12%">
            <th ><small>Nombre de tables</small></th>
            <td ><small><?php echo $nbTables; ?></small></td>
          </tr>
          <tr style="width:12%">
            <th><small>Nombre de Jurés</small></th>
            <td style=""><small><?php echo $nbDegustateurs; ?></small></td>
          </tr>
          <tr style="width:12%">
            <th><small>Nombre de jurés présents</small></th>
            <td style=""><small><?php echo $nbDegustateursPresents; ?></small></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

<hr>

<div>
  <table class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
    <tr>
      <td><div><p>IGP : <?php echo $appellation ?> </p></div></td>
      <td></td>
      <td>
        <div>
          <p>Tableau des échantillons de vin IGP présentés à la commission</p>
          <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
            <thead>
              <tr>
                <th rowspan="2" style="width:34%"></th>
                <th rowspan="2" style="width:33%"><small>Synthèse</small></th>
                <th colspan="2" style="width:33%"><small>Résultat</small></th>
              </tr>
              <tr>
                <th><small>C</small></th>
                <th><small>NC</small></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th><small>Nombre de lots</small></th>
                <td><small><?php echo $nbLotTotal ?></small></td>
                <td><small><?php echo $degustation->getNbLotsConformes() ?></small></td>
                <td><small><?php echo $degustation->getNbLotsNonConformes() ?></small></td>
              </tr>
              <tr>
                <th><small>Volumes total (hl)</small></th>
                <?php $volumeNC = $degustation->getVolumeLotsConformesOrNot(); $volumeC = $degustation->getVolumeLotsConformesOrNot(true) ?>
                <td><small><?php echo $volumeNC + $volumeC ?></small></td>
                <td><small><?php echo $volumeC ?></small></td>
                <td><small><?php echo $volumeNC ?></small></td>
              </tr>
              <tr>
                <th><small>Nombre d'opérateurs</small></th>
                <td><small><?php echo count($etablissements) ?></small></td>
                <td><small><?php echo count($degustation->getEtablissementLotsConformesOrNot(true)) ?></small></td>
                <td><small><?php echo count($degustation->getEtablissementLotsConformesOrNot()) ?></small></td>
              </tr>
            </tbody>
          </table>
        </div>
      </td>
    </tr>
    <tr>
      <td style="width: 100%">
        <div>
          <table border="0.5px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
            <tr>
              <th style="width: 5%"><?php echo tdStart() ?><small>N° DOssier ODG</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>N° Lot ODG</small></th>
              <th style="width: 20%"><?php echo tdStart() ?><small>Opérateur/Ville/CVI</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>N° Ano</small></th>
              <th style="width: 7%"><?php echo tdStart() ?><small>Contenant<br/>Logement<br/>Observations<br/>Déclaration</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>N° Lot Op</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>Volume<br/>(hl)</small></th>
              <th style="width: 8%"><?php echo tdStart() ?><small>Couleur</small></th>
              <th style="width: 10%"><?php echo tdStart() ?><small>Cépage</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>N° P</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>C/NC</small></th>
              <th style="width: 20%"><?php echo tdStart() ?><small>Motif NC <br/>Observation de Conformité</small></th>
            </tr>
          <?php foreach ($lotsDegustes as $key => $lotDeguste): ?>
            <tr>
              <td><small><?php echo $lotDeguste->numero_dossier ?></small></td>
              <td><small><?php echo $lotDeguste->numero_archive ?></small></td>
              <td><small><?php $etablissement = $etablissements[$lotDeguste->numero_dossier]; echo $etablissement->nom."<br/>".$etablissement->commune."<br/>".$etablissement->cvi ?></small></td>
              <td><small><?php echo "" ?></small></td>
              <td><small><?php echo "" ?></small></td>
              <td><small><?php echo $lotDeguste->numero_logement_operateur ?></small></td>
              <td style="float:right; text-align:right;"><small><?php echo number_format($lotDeguste->volume, 2) ?></small></td>
              <td><small><?php echo $lotDeguste->produit_libelle ?></small></td>
              <td><small><?php echo $lotDeguste->details ?></small></td>
              <td><small><?php echo "" ?></small></td>
              <td><small><?php echo $lotDeguste->statut == Lot::STATUT_CONFORME ? "C" : "NC" ?></small></td>
              <td><small><?php echo $lotDeguste->observation ?></small></td>
            </tr>
          <?php endforeach; ?>
          </table>
        </div>
      </td>
    </tr>
  </table>
</div>
