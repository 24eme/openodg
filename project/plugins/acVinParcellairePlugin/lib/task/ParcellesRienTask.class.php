<?php

class ParcellesRienTask extends sfBaseTask
{
    //php symfony intention-parcelles:import /home/moussa_24/Nextcloud/24eme/dpap2019_rien.csv /home/moussa_24/Nextcloud/24eme/dpap2019_rien_find_perfect.csv /home/moussa_24/Nextcloud/24eme/dpap2019_rien_find.csv --application="provence" --env="preprod" > ~/log_import

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_name', sfCommandArgument::REQUIRED, "Document name"),
            new sfCommandArgument('outputfileperfect', sfCommandArgument::REQUIRED, "file name of out perfect found"),
            new sfCommandArgument('outputfile', sfCommandArgument::REQUIRED, "file name of out"),
            
        ));
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'intention-parcelles';
        $this->name = 'import';
        $this->briefDescription = "Import de parcelles non trouvées";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        
        $fp = fopen($arguments["outputfile"], 'w');
        $fperfect = fopen($arguments["outputfileperfect"], 'w');
        $filename = $arguments["doc_name"];
        $nb_ok = 0;
        $nb_false = 0;
        $nb = 0;
        $nb_perfect = 0;
        $parcellefill = [];
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $idu = $data[1];
                $nb++;
                $parcelles = TmpParcellesView::getInstance()->findByIdu($idu);
                if(!empty($parcelles)){
                    $nb_ok++;
                    foreach ($parcelles as $parcelle) {
                        $superficie = floatval(str_replace(",",".",$data[2]));
                        $key = $parcelle->id."-".$idu."-".str_replace(" ", "-", $parcelle->value[3])."-".str_replace(".", "-", $parcelle->value[4]);
                        if($superficie == floatval($parcelle->value[4])){
                            $nb_perfect++;
                            echo sprintf("Success;Trouvé;%s;%s;%s;%s;%s\n", $data[0], $idu, $superficie, floatval($parcelle->value[4]),$data[4]);

                            $parcellefill[$key] = [$data[0], $idu, $parcelle->id, $parcelle->value[3], $parcelle->value[4],$superficie];
                            fputcsv($fperfect, [$data[0], $idu, $parcelle->id, $parcelle->value[3], $parcelle->value[4], $superficie]);
                        }else{
                            echo sprintf("Warning;idu Trouvé mais surface diff;%s;%s;%s;%s;%s\n", $data[0], $idu, $superficie, floatval($parcelle->value[4]),$data[4]);
                            fputcsv($fp, [$data[0], $idu, $parcelle->id, $parcelle->value[3], $parcelle->value[4],$superficie]);
                        }
                    }
                }else{
                    $nb_false++;
                    echo sprintf("ERROR;Idu non trouvé;%s;%s;%s;%s;%s\n", $idu, $data[0], $data[2], $data[3], $data[4]);
                }
            }
            fclose($fperfect);
            fclose($fp);
            fclose($handle);
        }
        echo sprintf("Found perfect: %s / %s\nFound: %s / %s\nNot found : %s / %s\n", $nb_perfect, $nb, $nb_ok, $nb, $nb_false, $nb);
    }
    
}
