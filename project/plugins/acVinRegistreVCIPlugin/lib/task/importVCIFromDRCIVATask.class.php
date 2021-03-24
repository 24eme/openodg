<?php

class importVCIFromDRCIVATask extends sfBaseTask
{

  const DRCIVA_CVI_ACHETEUR = 0;
  const DRCIVA_NOM_ACHETEUR = 1;
  const DRCIVA_CVI_RECOLTANT = 2;
  const DRCIVA_NOM_RECOLTANT = 3;
  const DRCIVA_APPELLATION = 4;
  const DRCIVA_LIEU = 5;
  const DRCIVA_CEPAGE = 6;
  const DRCIVA_VTSGN = 7;
  const DRCIVA_DENOMINATION = 8;
  const DRCIVA_SUPERFICIE = 9;
  const DRCIVA_VOLUME = 10;
  const DRCIVA_DONT_VOLUME_A_DETRUIRE = 11;
  const DRCIVA_SUPERFICIE_TOTALE = 12;
  const DRCIVA_VOLUME_TOTAL = 13;
  const DRCIVA_VOLUME_A_DETRUIRE_TOTAL = 14;
  const DRCIVA_DONT_VCI = 15;
  const DRCIVA_VCI_TOTAL = 16;
  const DRCIVA_DATE_DE_VALIDATION = 17;
  const DRCIVA_DATE_DE_MODIFICATION = 18;
  const DRCIVA_VALIDATEUR = 19;
  const DRCIVA_HASH_PRODUIT = 20;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, "Campagne du VCI"),
            new sfCommandArgument('fichier_dr_civa', sfCommandArgument::REQUIRED, "Fichier csv des DR"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'VCIFromDR';
        $this->briefDescription = 'Import les VCI (via le csv des DR du CIVA)';
        $this->detailedDescription = <<<EOF
EOF;
    }


    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $datas = array();
        $date_dossiers = array();
        foreach(file($arguments['fichier_dr_civa']) as $line) {
          $line = str_replace('\\', '', str_replace('"', '', $line));
          $line = rtrim($line);
          $csv = explode(';', $line);

          if (($csv[self::DRCIVA_APPELLATION] != 'AOC Alsace blanc') && ($csv[self::DRCIVA_APPELLATION] != 'AOC Cremant d\'Alsace')) {
            continue;
          }
          if ($csv[self::DRCIVA_VTSGN]) {
            continue;
          }

          if ($csv[self::DRCIVA_CVI_ACHETEUR] == $csv[self::DRCIVA_CVI_RECOLTANT]) {
            $csv[self::DRCIVA_CVI_ACHETEUR] = '';
          }

          if ($csv[self::DRCIVA_LIEU] == 'TOTAL') {
            @$vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU']['TOTAL']['']['CEPAGE']['']['SUPERFICIE'] += $csv[self::DRCIVA_SUPERFICIE_TOTALE];
          }

          $oldreporting = error_reporting(0);
	  if (($csv[self::DRCIVA_LIEU] == 'TOTAL') || (preg_match("/^TOTAL/", $csv[self::DRCIVA_CEPAGE]))) {
              $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU'][''][$csv[self::DRCIVA_CVI_ACHETEUR]]['CEPAGE']['TOTAL']['DONTVCI'] = $csv[self::DRCIVA_DONT_VCI];
              $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU']['TOTAL']['']['CEPAGE']['']['DONTVCI'] += $csv[self::DRCIVA_DONT_VCI];
          }
	  if (($csv[self::DRCIVA_LIEU] != 'TOTAL') && !preg_match("/^TOTAL/", $csv[self::DRCIVA_CEPAGE])) {
            $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU']['TOTAL']['']['CEPAGE']['']['VOLUME_TOTAL'] += $csv[self::DRCIVA_VCI_TOTAL];
            $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU'][$csv[self::DRCIVA_LIEU]][$csv[self::DRCIVA_CVI_ACHETEUR]]['CEPAGE']['TOTAL']['VOLUME_TOTAL'] += $csv[self::DRCIVA_VCI_TOTAL];
          }
          $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU'][$csv[self::DRCIVA_LIEU]][$csv[self::DRCIVA_CVI_ACHETEUR]]['CEPAGE'][$csv[self::DRCIVA_CEPAGE]]['VOLUME'] += $csv[self::DRCIVA_VCI_TOTAL];
          $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU'][$csv[self::DRCIVA_LIEU]][$csv[self::DRCIVA_CVI_ACHETEUR]]['CEPAGE'][$csv[self::DRCIVA_CEPAGE]]['ACHETEUR_CVI'] = $csv[self::DRCIVA_CVI_ACHETEUR];
          $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU'][$csv[self::DRCIVA_LIEU]][$csv[self::DRCIVA_CVI_ACHETEUR]]['CEPAGE'][$csv[self::DRCIVA_CEPAGE]]['ACHETEUR_NOM'] = $csv[self::DRCIVA_NOM_ACHETEUR];
          $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU'][$csv[self::DRCIVA_LIEU]][$csv[self::DRCIVA_CVI_ACHETEUR]]['CEPAGE'][$csv[self::DRCIVA_CEPAGE]]['HASH_PRODUIT'] = $csv[self::DRCIVA_HASH_PRODUIT];
          $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU'][$csv[self::DRCIVA_LIEU]][$csv[self::DRCIVA_CVI_ACHETEUR]]['CEPAGE'][$csv[self::DRCIVA_CEPAGE]]['RECOLTANT_CVI'] =  $csv[self::DRCIVA_CVI_RECOLTANT];
          $vci[$csv[self::DRCIVA_CVI_RECOLTANT]][$csv[self::DRCIVA_APPELLATION]]['LIEU'][$csv[self::DRCIVA_LIEU]][$csv[self::DRCIVA_CVI_ACHETEUR]]['CEPAGE'][$csv[self::DRCIVA_CEPAGE]]['RECOLTANT_NOM'] =  $csv[self::DRCIVA_NOM_RECOLTANT];
          error_reporting($oldreporting);
        }
        //On vire les données qui n'ont pas de VCI (et seulement des superficies)
        $realvci = array();
        foreach ($vci as $recoltant => $vciappellation) {
            $nb = 0;
            foreach($vciappellation as $appellation => $vcilieu) {
              foreach($vcilieu['LIEU'] as $lieu => $vciacheteur) {
                foreach($vciacheteur as $cviacheteur => $vcicepage) {
                  foreach($vcicepage['CEPAGE'] as $cepage => $unvci) {
                      if($cepage == "TOTAL" && $vci[$recoltant][$appellation]['LIEU']['TOTAL']['']['CEPAGE']['']['DONTVCI'] && (!isset($unvci['DONTVCI']) || !$unvci['DONTVCI']) && isset($unvci['VOLUME_TOTAL']) && $unvci['VOLUME_TOTAL']) {
                          $vci[$recoltant][$appellation]['LIEU']['TOTAL']['']['CEPAGE']['']['VOLUME_TOTAL'] -= $unvci['VOLUME_TOTAL'];
                          unset($vci[$recoltant][$appellation]['LIEU'][$lieu][$cviacheteur]);
                          break;
                      }
                      $nb += 1;
                  }
                }
              }
            }
            if ($nb > 2) {
              $realvci[$recoltant] = $vci[$recoltant];
            }
        }
        //Enregistrement des données
        foreach ($realvci as $recoltant => $vciappellation) {
            try {
                $registre = RegistreVCIClient::getInstance()->findMasterByIdentifiantAndCampagneOrCreate($recoltant."",  $arguments['campagne']);
            } catch(Exception $e) {
                $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($recoltant."", $arguments['campagne'], acCouchdbClient::HYDRATE_JSON);
                echo $recoltant . " : " . $e->getMessage()." ".(($drev && $drev->validation_odg) ? $drev->_id : null)."\n";
                continue;
            }
            if (!$registre->isNew()) {
                continue;
            }
            foreach($vciappellation as $appellation => $vcilieu) {
            $nonsolvable = 0;
            $totalappellation = $vcilieu['LIEU']['TOTAL']['']['CEPAGE'][''];
            if (!isset($totalappellation['VOLUME']) || !(sprintf('%0.2f', $totalappellation['VOLUME_TOTAL']) === sprintf('%0.2f', $totalappellation['VOLUME']))) {
              $nonsolvable = 1;
            }
            foreach($vcilieu['LIEU'] as $lieu => $vciacheteur) {
              if ($lieu == 'TOTAL') {
                continue;
              }
              foreach($vciacheteur as $cviacheteur => $vcicepage) {
                foreach($vcicepage['CEPAGE'] as $cepage => $unvci) {
                  if (preg_match('/^TOTAL/', $cepage)) {
                    continue;
                  }
                  if ($nonsolvable) {
                    echo "NONSOLVABLE VCI";
                    echo $unvci['RECOLTANT_NOM']." ".$recoltant." (".$unvci['HASH_PRODUIT'].") ".$unvci['VOLUME']." hl ".$unvci['ACHETEUR_NOM']."\n";
                    continue;
                  }
                  if ($unvci['VOLUME'] * 1.0 > 0) {
                    echo "$recoltant : add ".preg_replace('/\/detail\/\d+/', '', str_replace('/recolte/', '/declaration/', $unvci['HASH_PRODUIT']))." ".$unvci['VOLUME'] ." ";
                    echo ($unvci['ACHETEUR_CVI'] && $unvci['ACHETEUR_CVI'] != $unvci['RECOLTANT_CVI']) ? $unvci['ACHETEUR_NOM'] : RegistreVCIClient::LIEU_CAVEPARTICULIERE;
                    echo "\n";

                    $registre->addLigne(preg_replace('/\/detail\/\d+/', '', str_replace('/recolte/', '/declaration/', $unvci['HASH_PRODUIT'])), RegistreVCIClient::MOUVEMENT_CONSTITUE, $unvci['VOLUME'] * 1.0, ($unvci['ACHETEUR_CVI'] && $unvci['ACHETEUR_CVI'] != $unvci['RECOLTANT_CVI']) ? $unvci['ACHETEUR_CVI'] : RegistreVCIClient::LIEU_CAVEPARTICULIERE);
                  }
                }
              }
            }
          }
          if ((!$registre->exist('lignes') || !count($registre->lignes)) && !$registre->getStockFinalTotal() && !$registre->getStockPrecedentTotal()) {
              continue;
          }

          $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($recoltant."", $arguments['campagne'], acCouchdbClient::HYDRATE_JSON);
          if(!$drev || !$drev->validation_odg) {
              echo "Pas de drev ou pas validé : ".$recoltant."\n";
              continue;
          }

          $registre->save();
          echo "Superficie facturable : ".$registre->superficies_facturables."\n";
          echo $registre->_id." savé\n";
        }
    }
}
