<?php

class ImportDocumentsDouaniersTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('annee', sfCommandArgument::REQUIRED, "Campagne au format AAAA"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),

        	  new sfCommandOption('type', null, sfCommandOption::PARAMETER_OPTIONAL, "Type de document", null),
            new sfCommandOption('limitidentifiant', null, sfCommandOption::PARAMETER_OPTIONAL, "Limit à l'identifiant indiqué", null),
            new sfCommandOption('forceimport', null, sfCommandOption::PARAMETER_OPTIONAL, "Force import document (exept if manualy edited)", false),
            new sfCommandOption('scrapefiles', null, sfCommandOption::PARAMETER_OPTIONAL, "Scrape import document", false),
            new sfCommandOption('dateimport', null, sfCommandOption::PARAMETER_OPTIONAL, "Date d'import", null),
            new sfCommandOption('diff', null, sfCommandOption::PARAMETER_OPTIONAL, "Diff", false),
            new sfCommandOption('debug', null, sfCommandOption::PARAMETER_OPTIONAL, "Debug", false),
        ));

        $this->namespace = 'import';
        $this->name = 'documents-douaniers';
        $this->briefDescription = "Import des documents douaniers";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $contextInstance = sfContext::createInstance($this->configuration);

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $context = sfContext::createInstance($this->configuration);

        $annee = $arguments['annee'];
        $type = ($options['type'])? strtoupper($options['type']) : null;

        if (!preg_match('/^[0-9]{4}$/', $annee)) {
        	echo sprintf("ERROR;Année non valide %s\n", $annee);
        	return;
        }

        if ($type && !in_array($type, $this->getDocumentDouanierTypes())) {
        	echo sprintf("ERROR;Type non valide %s\n", $type);
        	return;
        }

        $items = EtablissementAllView::getInstance()->findByInterproStatutAndFamilleVIEW('INTERPRO-declaration', 'ACTIF', null);
		    $i=0;
		    $nb = count($items);
        foreach ($items as $item) {

            if ($options['limitidentifiant'] && strpos($item->id, $options['limitidentifiant']) === false) {
                continue;
            }

        	$i++;

        	if ($etablissement = EtablissementClient::getInstance()->find($item->id)) {
                $ddType = DouaneCsvFile::getDocumentDouanierType($etablissement);

        		if ($type && $ddType != $type) {
        			continue;
        		}

                if ($etablissement->statut != CompteClient::STATUT_ACTIF) {
                    continue;
                }

        		if (!$etablissement->cvi || !preg_match('/^[0-9]{9}[0-9A-Z]$/', $etablissement->cvi)) {
        			echo sprintf("ERROR;CVI non valide %s pour %s\n", $etablissement->cvi, $etablissement->_id);
        			continue;
        		}

        		if (!$ddType) {
        			echo sprintf("ERROR;Famille non identifiée %s pour %s\n", $etablissement->famille, $etablissement->_id);
        			continue;
        		}

        		$c = FichierClient::getInstance()->getClientFromType($ddType);

                if (($f = $c->findByArgs($etablissement->identifiant, $annee)) && (!($options['forceimport']) || ($f && $f->exist('donnees')))) {
                    if (!$options['forceimport']) {
                        if ($options['diff']) {
                            $old = [];
                            $new = [];
                            $diff = DouaneCsvFile::getDiffWithScrapyFile($f, $old, $new);
                            if ($options['debug']) {
                                print_r(['old' => $old, 'new' => $new, 'diff' => $diff]);
                            }
                            if ($nb = count($diff)) {
                                echo sprintf("WARNING;Document douanier déjà existant %s et le fichier en base ne semble pas à jour : %d diff\n", $f->_id, $nb);
                            }else{
                                echo sprintf("WARNING;Document douanier déjà existant %s (no diff)\n", $f->_id);
                            }
                            continue;
                        }
                        echo sprintf("WARNING;Document douanier déjà existant %s\n", $f->_id);
                    }else{
                        echo sprintf("WARNING;Document douanier en saisie interne %s\n", $f->_id);
                    }
                    continue;
                }

                try {
                    if (strtoupper($options['scrapefiles']) == "FALSE") {
                        $options['scrapefiles'] = false;
                    }
                    $fichiers = FichierClient::getInstance()->scrapeAndSaveFiles($etablissement, $ddType, $annee, ($options['scrapefiles']), $contextInstance);
                } catch (Exception $e) {
                    if ($options['debug']) {
                        throw $e;
                    }
                    echo sprintf("ERROR;%s\n", $e->getMessage());
                    continue;
                }

        		if (!$fichiers) {
        			echo sprintf("WARNING;Aucun document douanier pour %s (%s)\n", $etablissement->_id, $etablissement->cvi);
        		} else {
                    foreach($fichiers as $fichier) {
                        if(isset($options['dateimport']) && $options['dateimport']) {
                            $fichier->date_import = $options['dateimport'];
                            $fichier->date_depot = $options['dateimport'];
                            if (DRConfiguration::getInstance()->hasValidationDR()) {
                                $fichier->add('validation_odg', $options['dateimport']);
                            }
                            $fichier->save();
                        }
			            echo sprintf("SUCCESS;Document douanier importé;%s %s (%s)\n", $fichier->type, $etablissement->_id, $etablissement->cvi);
                    }
        		}

        	} else {
        		echo sprintf("ERROR;Etablissement non trouvé %s\n", $item->id);
        	}
        }
    }

    protected function getDocumentDouanierTypes()
    {
        return array(
            DRCsvFile::CSV_TYPE_DR,
            SV11CsvFile::CSV_TYPE_SV11,
            SV12CsvFile::CSV_TYPE_SV12
        );
    }

}
