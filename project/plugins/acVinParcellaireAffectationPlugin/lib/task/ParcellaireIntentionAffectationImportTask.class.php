<?php

class ParcellaireIntentionAffectationImportTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "CSV intentions dpap"),
            new sfCommandArgument('date', sfCommandArgument::REQUIRED, "date intention dpap"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'intention-dpap';
        $this->name = 'import';
        $this->briefDescription = "Import de l'intention dpap";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        if(!file_exists($arguments['csv'])) {
            echo sprintf("ERROR;Le fichier CSV n'existe pas;%s\n", $arguments['csv']);
            return;
        }
        if (!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $arguments['date'], $m)) {
            echo sprintf("ERROR;Le format date n'est pas valide (yyyy-mm-dd);%s\n", $arguments['date']);
            return;
        }
        $campagne = $m[1];
        

        $csvFile = new CsvFile($arguments['csv']);
        $csv = $csvFile->getCsv();
        foreach($csv as $ligne => $data) {
            $etablissement = EtablissementClient::getInstance()->findByIdentifiant($data[0]);
            if (!$etablissement) {
                echo sprintf("ERROR;Etablissement non trouvé;%s\n", $data[0]);
                continue;
            }
            $intentionDpap = ParcellaireIntentionAffectationClient::getInstance()->createDoc($data[0], $campagne, 1, $arguments['date']);
            if (!$intentionDpap->hasParcellaire()) {
                echo sprintf("ERROR;Pas de parcellaire pour ;%s\n", $data[0]);
                continue;
            }
            
            $idu = $data[1];
            $surface = $this->formatFloat($data[2]);
            $cepage = $data[3];
            $dgc = $data[4];
            
            $parcelles = $intentionDpap->getParcelles();
            $findParfait = false;
            $findIduCepage = false;
            $findIdu = false;
            foreach ($parcelles as $parcelle) {
                if ($parcelle->idu == $idu) {
                    $findIdu = true;
                }
                if ($parcelle->idu == $idu && $parcelle->cepage == $cepage) {
                    $findIduCepage = true;
                }
                if ($parcelle->idu == $idu && $parcelle->cepage == $cepage && $parcelle->superficie == $surface) {
                    $findParfait = true;
                    break;
                }
            }
            if (!$findParfait) {
                if ($findIduCepage) {
                    echo sprintf("ERROR;Idu et cepage trouvés surface non identifié;%s;%s %s %s\n", $data[0], $idu, $cepage, $surface);
                    continue;
                } elseif ($findIdu) {
                    echo sprintf("ERROR;Idu trouvé cepage et surface non identifié;%s;%s %s %s\n", $data[0], $idu, $cepage, $surface);
                    continue;
                } else {
                    echo sprintf("ERROR;Parcelle non identifiée;%s;%s %s %s\n", $data[0], $idu, $cepage, $surface);
                    continue;
                }
            }
        }

    }

    protected function looping($parcelleAire, $parcelles, $index, $surface){
        $find = false;
        if($index == $count($parcelles)){
            return $find;
        }
        for($i = $index+1; $i < $count($parcelles); $i++) {
            if(($parcelleAire + $parcelles[$i]->superficie) == $surface){
                return true;
            }
            $find = looping(($parcelleAire + $parcelles[$i]->superficie), $parcelles, $i, $surface);
        }
        return $find;
    }

    protected function formatFloat($value) {

        return str_replace(',', '.', $value)*1.0;
    }
}
