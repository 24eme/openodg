<?php

class DRevAdaptationSuperficieVinifieeTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'adaptation-superficie-vinifiee';
        $this->briefDescription = "Sauvegarde de la DRev";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();


        $drev = DRevClient::getInstance()->find($arguments['doc_id']);

        if(!$drev) {
            echo sprintf("ERROR;DRev introuvable %s\n", $arguments['doc_id']);
            return;
        }

        if($drev->isLectureSeule()) {
            return;
        }

        foreach($drev->declaration->getProduits() as $produit) {
            $produit->add('superficie_vinifiee');
            if($produit->getConfig()->hasProduitsVtsgn()) {
                $produit->add('superficie_vinifiee_vtsgn');
            }

            foreach($produit->getProduitsCepage() as $detail) {
                $detail->add('superficie_vinifiee_total');
                $detail->add('superficie_vinifiee');
                $detail->add('superficie_vinifiee_vt');
                $detail->add('superficie_vinifiee_sgn');
            }
        }

        $drev->save();

        echo sprintf("SUCCESS;La DRev a bien été sauvegardée avec les superficies vinifiéé dans le model;%s\n", $drev->_id);
    }
}
