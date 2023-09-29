<?php

class ParcellaireAffectationImportTask extends sfBaseTask
{
    public $combinaisons;
    public $currentCombinaison;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "CSV intentions dpap"),
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, "Campagne"),
        ));
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));
        $this->namespace = 'affectation-dpap';
        $this->name = 'import';
        $this->briefDescription = "Import de l'affectation dpap";
        $this->detailedDescription = "";
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        
        $campagne = $arguments['campagne'];
        
        if(!file_exists($arguments['csv'])) {
            echo sprintf("ERROR;Le fichier CSV n'existe pas;%s\n", $arguments['csv']);
            return;
        }
        if (!preg_match('/^[0-9]{4}$/', $campagne)) {
            echo sprintf("ERROR;Format campagne (AAAA) non valide;%s\n", $campagne);
            exit;
        }
        
        $csvFile = new CsvFile($arguments['csv']);
        $csv = $csvFile->getCsv();
        $index = 0;
        foreach($csv as $ligne => $data) {
            $identifiant = $data[0];
            $idu = $data[1];
            $surface = round($this->formatFloat($data[2]),4);
            $cepage = $data[3];
            $dgc = $data[4];
            
            $identifiantIdu = null;
            $items = TmpParcellesView::getInstance()->findByIdu($idu);
            foreach ($items as $item) {
                if (preg_match('/^PARCELLAIRE-(.+)-[0-9]{8}$/', $item->id, $m)) {
                    $identifiantIdu = $m[1];
                    break;
                }
            }
            if ($identifiantIdu && $identifiantIdu != $identifiant) {
                $identifiant = $identifiantIdu;
            }
            $etablissement = EtablissementClient::getInstance()->findByIdentifiant($identifiant);
            if (!$etablissement) {
                echo sprintf("ERROR;Etablissement non trouvé;%s\n", implode(';', $data));
                continue;
            }

            if ($intentionAffectation = ParcellaireIntentionClient::getInstance()->getLast($etablissement->identifiant, $campagne)) {
                $affectation = ParcellaireAffectationClient::getInstance()->findOrCreate($etablissement->identifiant, $campagne, true);
                $found = false;
                foreach ($affectation->getParcelles() as $parcelle) {
                    if ($parcelle->affectation && $parcelle->date_affectation && $parcelle->idu == $idu && $parcelle->cepage == $cepage && round($parcelle->superficie,4) == $surface) {
                        $parcelle->affectee = 1;
                        $found = true;
                    }
                }
                if (!$found) {
                    foreach ($affectation->getParcelles() as $parcelle) {
                        if ($parcelle->affectation && $parcelle->date_affectation && $parcelle->idu == $idu && $parcelle->cepage == $cepage) {
                            $parcelle->affectee = 1;
                            $found = true;
                        }
                    }
                }
                if (!$found) {
                    foreach ($affectation->getParcelles() as $parcelle) {
                        if ($parcelle->affectation && $parcelle->date_affectation && $parcelle->idu == $idu) {
                            $parcelle->affectee = 1;
                            $found = true;
                        }
                    }
                }
                if ($found) {
                    $affectation->validation = "$campagne-12-31";
                    $affectation->validation_odg = "$campagne-12-31";
                    $affectation->save();
                    echo sprintf("SUCCESS;%s validée avec succès\n", $affectation->_id);
                } else {
                    echo sprintf("ERROR;Parcelle non identifiée;%s\n", implode(';', $data));
                }
            } else {
                echo sprintf("ERROR;Intention inexistante;%s;%s\n", $etablissement->identifiant, $campagne);
            }
            
        }
        
    }

    protected function formatFloat($value) {
    
        return str_replace(',', '.', $value)*1.0;
    }
    
}
