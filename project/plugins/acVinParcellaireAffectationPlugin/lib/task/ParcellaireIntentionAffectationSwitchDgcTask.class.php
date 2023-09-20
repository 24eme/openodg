<?php

class ParcellaireIntentionAffectationSwitchDgcTask extends sfBaseTask
{
    public $combinaisons;
    public $currentCombinaison;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "CSV intentions dpap"),
        ));
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));
        $this->namespace = 'intention-dpap';
        $this->name = 'switch-dgc';
        $this->briefDescription = "Switch DGC de l'intention dpap";
        $this->detailedDescription = "";
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        if(!file_exists($arguments['csv'])) {
            echo sprintf("ERROR;Le fichier CSV n'existe pas;%s\n", $arguments['csv']);
            return;
        }
        $csvFile = new CsvFile($arguments['csv']);
        $csv = $csvFile->getCsv();
        $index = 0;
        foreach($csv as $ligne => $data) {
            $idu = $data[0];
            $surface = round($this->formatFloat($data[1]),4);
            $dgc = $data[2];
            
            
            $items = TmpParcellesView::getInstance()->findByIdu($idu);
            foreach ($items as $item) {
                if (preg_match('/^PARCELLAIRE-(.+)-[0-9]{8}$/', $item->id, $m)) {
                    $identifiant = $m[1];
                    break;
                }
            }
            if (!$identifiant) {
                echo sprintf("ERROR;Identifiant non trouvé;%s\n", implode(';', $data));
                continue;
            }
            $etablissement = EtablissementClient::getInstance()->findByIdentifiant($identifiant);
            if (!$etablissement) {
                echo sprintf("ERROR;Etablissement non trouvé;%s\n", implode(';', $data));
                continue;
            }
            $intentionDpap = ParcellaireIntentionClient::getInstance()->getLast($identifiant);
            if (!$intentionDpap) {
                echo sprintf("ERROR;Pas de parcellaire intention affectation;%s\n", implode(';', $data));
                continue;
            }
            $parcelles = $intentionDpap->getParcellesByIduSurface($idu, $surface);
            if (count($parcelles) < 1) {
                echo sprintf("ERROR;Aucune parcelles trouvées;%s;%s\n", $intentionDpap->_id, implode(';', $data));
                continue;
            }
            $parcelle = current($parcelles);
            $newHash = explode('/', str_replace('/declaration/', '',$parcelle->getProduit()->getHash()));
            $newHash[count($newHash) - 1] = $dgc;
            $newHash = implode('/', $newHash);
            $prod = $intentionDpap->declaration->add($newHash);
            $prod->libelle = "Côtes de Provence ".$prod->getConfig()->getLibelle();
            $detail = $prod->detail->add($parcelle->getKey(), $parcelle);
            if (count($parcelle->getProduit()->detail) > 1) { 
                $intentionDpap->declaration->remove(str_replace('/declaration/', '',$parcelle->getHash()));
            } else {
                $intentionDpap->declaration->remove(str_replace('/declaration/', '',$parcelle->getProduit()->getHash()));
            }
            $intentionDpap->save();
            echo sprintf("SUCCESS;OK;%s\n", $intentionDpap->_id);
        }
    }
    
    protected function formatFloat($value) {

        return str_replace(',', '.', $value)*1.0;
    }
}
