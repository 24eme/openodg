<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Date'); ?>

<style>
<?php echo style(); ?>

th {
  background-color:white;
}

</style>

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
              <th colspan="2" style="width:20%"><small>Résultat</small></th>
            </tr>
            <tr>
              <th><small>C</small></th>
              <th><small>NC</small></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th><small>Nombre de lots</small></th>
              <td><small><?php echo $degustation->getNbLotsConformes() + $degustation->getNbLotsNonConformes() ?></small></td>
              <td><small><?php echo $degustation->getNbLotsConformes() ?></small></td>
              <td><small><?php echo $degustation->getNbLotsNonConformes() ?></small></td>
            </tr>
            <tr>
              <th><small>Volumes total (hl)</small></th>
              <?php $volumeNC = $degustation->getVolumeLotsConformesOrNot(false); $volumeC = $degustation->getVolumeLotsConformesOrNot(true) ?>
              <td style="text-align: right"><small><?php echo $volumeNC + $volumeC ?> hl</small>&nbsp;&nbsp;</td>
              <td style="text-align: right"><small><?php echo $volumeC ?> hl</small>&nbsp;&nbsp;</td>
              <td style="text-align: right"><small><?php echo $volumeNC ?> hl</small>&nbsp;&nbsp;</td>
            </tr>
            <tr>
              <th><small>Nombre d'opérateurs</small></th>
              <td><small><?php echo count($etablissements) ?></small></td>
              <td><small><?php echo count($degustation->getEtablissementLotsConformesOrNot(true)) ?></small></td>
              <td><small><?php echo count($degustation->getEtablissementLotsConformesOrNot(false)) ?></small></td>
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
            <th><small>Nombre de jurés</small></th>
            <td style=""><small><?php echo $nbDegustateursPresents; ?></small></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

<p></p>
<hr>

<div>
  <table class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse; margin-top: 1px" scope="colgroup" >
    <tr>
      <td><div><p>Tableau des échantillons de vin IGP <?php echo $appellation ?> présentés à la commission</p></div></td>
      <td>
        <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
          <thead>
            <tr>
              <th rowspan="2" style="width:35%"></th>
              <th rowspan="2" style="width:15%"><small>Synthèse</small></th>
              <th colspan="2" style="width:20%"><small>Résultat</small></th>
            </tr>
            <tr>
              <th><small>C</small></th>
              <th><small>NC</small></th>
            </tr>
          </thead>
          <tbody>
            <?php $c = []; $nc = []; $vc = 0; $vnc = 0; foreach ($lotsDegustes as $l) {
                if ($l->conformite == Lot::CONFORMITE_CONFORME) :
                    $c[$l->declarant_identifiant]++; $vc += $l->volume;
                else:
                    $nc[$l->declarant_identifiant]++; $vnc += $l->volume;
                endif;
            } ?>
            <tr>
              <th><small>Nombre de lots</small></th>
              <td><small><?php echo count($lotsDegustes) ?></small></td>
              <td><small><?php echo array_sum($c) ?></small></td>
              <td><small><?php echo array_sum($nc) ?></small></td>
            </tr>
            <tr>
              <th><small>Volumes total (hl)</small></th>
              <td style="text-align: right"><small><?php echo $vc + $vnc ?> hl</small></td>
              <td style="text-align: right"><small><?php echo $vc ?> hl</small></td>
              <td style="text-align: right"><small><?php echo $vnc ?> hl</small></td>
            </tr>
            <tr>
              <th><small>Nombre d'opérateurs</small></th>
              <td><small><?php echo count($c) + count($nc) ?></small></td>
              <td><small><?php echo count($c) ?></small></td>
              <td><small><?php echo count($nc) ?></small></td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
      <td style="width: 100%">
        <div>
          <table border="0.5px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
            <tr>
              <th style="width: 5%"><?php echo tdStart() ?><small>N° Dossier ODG</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>N° Lot ODG</small></th>
              <th style="width: 20%"><?php echo tdStart() ?><small>Opérateur/Ville/CVI</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>N° Ano</small></th>
              <th style="width: 10%"><?php echo tdStart() ?><small>N° Logement Op</small></th>
              <th style="width: 20%"><?php echo tdStart() ?><small>Produit</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>Volume<br/>(hl)</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>N° P</small></th>
              <th style="width: 5%"><?php echo tdStart() ?><small>C/NC</small></th>
              <th style="width: 20%"><?php echo tdStart() ?><small>Motif NC <br/>Observation de Conformité</small></th>
            </tr>
          <?php $page1 = 0; $pages = 0; foreach ($lotsDegustes as $key => $lotDeguste): ?>
          <?php if ($page1 == 7 || $pages == 15): ?>
            <tr pagebreak="true">
          <?php $pages = 0; ?>
          <?php else: ?>
            <tr>
          <?php endif ?>
              <td><small><?php echo $lotDeguste->numero_dossier ?></small></td>
              <td><small><?php echo $lotDeguste->numero_archive ?></small></td>
              <td><small><?php $etablissement = $etablissements[$lotDeguste->declarant_identifiant]; echo $etablissement->nom."<br/>".$etablissement->commune."<br/>".$etablissement->cvi ?></small></td>
              <td><small><?php echo $lotDeguste->numero_anonymat ?></small></td>
              <td><small><?php echo $lotDeguste->numero_logement_operateur ?></small></td>
              <td><small><?php echo $lotDeguste->produit_libelle . ' ' . $lotDeguste->millesime . ' ' . showOnlyCepages($lotDeguste) ?></small></td>
              <td style="float:right; text-align:right;"><small><?php echo number_format($lotDeguste->volume, 2) ?> hl</small></td>
              <td><small><?php echo $lotDeguste->getTextPassage() ?></small></td>
              <td><small><?php echo $lotDeguste->statut == Lot::STATUT_CONFORME ? "C" : "NC" ?></small></td>
              <td><small>
                <?php if ($lotDeguste->getMouvement(Lot::STATUT_CONFORME)): ?>
                    <?php echo $lotDeguste->observation ?>
                <?php else: ?>
                    <?php echo Lot::getLibelleConformite($lotDeguste->conformite) ?> :
                    <?php echo $lotDeguste->motif ?>
                <?php endif ?>
              </small></td>
            </tr>
          <?php $page1++; $pages++; ?>
          <?php endforeach; ?>
          </table>
        </div>
      </td>
    </tr>
  </table>
</div>
