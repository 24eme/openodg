<?php

class fixSuperficieVTTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'superficieVT';
        $this->briefDescription = "";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $registre = RegistreVCIClient::getInstance()->find($arguments['doc_id']);

        $superficie = $registre->superficies_facturables;
        $newSuperficie = $registre->calculSurfaceFacturable();

        if($superficie == $newSuperficie) {
            return;
        }

        $superficieDiffHa = round(($newSuperficie - $superficie) / 100, 2);

        $factures = FactureClient::getInstance()->getFacturesByCompte('E'.$registre->identifiant, acCouchdbClient::HYDRATE_DOCUMENT);

        $facture = null;
        foreach($factures as $f) {
            if($f->exist('origines/'.$registre->_id)) {

                $facture = $f;
                break;
            }
        }

        if(!$facture) {

            return;
        }

        $avoir = FactureClient::createAvoir($facture);

        foreach($avoir->lignes as $ligne) {
            foreach($ligne->details as $detail) {
                $detail->quantite = 0;
            }
        }

        $avoir->lignes->vci->details[0]->quantite = $superficieDiffHa;
        $avoir->updateTotaux();

        $avoir->save();

        echo $avoir->_id.";".$superficieDiffHa."\n";

        $registre->save();

    }
}
