<?php

class DRImportRelationBailleurTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "DR document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'dr';
        $this->name = 'import-relation-bailleur';
        $this->briefDescription = "Importe les relations bailleurs / metayer à partir d'une dr";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $declaration_production = DRClient::getInstance()->find($arguments['doc_id']);
        $etablissement_source = $declaration_production->getEtablissementObject();
        $etablissements_bailleur = $declaration_production->getBailleurs();
        $tiers = array();
        if ($etablissements_bailleur) {
            $this->getBailleurRelation($etablissement_source, $etablissements_bailleur);
        }
        if ($declaration_production->isApporteur()) {
            $tiers = $declaration_production->getTiers(false, "NEGOCIANT_VINIFICATEUR");
            $this->setTiersRelation($etablissement_source, $tiers, "NEGOCIANT_VINIFICATEUR");
        }
        if ($declaration_production->hasApporteurs()) {
            $tiers = $declaration_production->getApporteurs(true);
            if ($declaration_production["type"] === "SV11") {
                $this->setTiersRelation($etablissement_source, $tiers, "COOPERATEUR");
            }
            if ($declaration_production["type"] === "SV12") {
                $this->setTiersRelation($etablissement_source, $tiers, "APPORTEUR_RAISIN");
            }
        }
    }

    public function setTiersRelation($etablissement_source, $tiers, $relation) {
        foreach ($tiers as $etablissement) {
            $etablissement_source->addLiaison($relation, EtablissementClient::getInstance()->find($etablissement['etablissement']->_id), true);
        }
        $etablissement_source->save();
    }


    public function getBailleurRelation($etablissement_source, $etablissements_bailleur) {
        foreach ($etablissements_bailleur as $etablissement) {
            $etablissement_id = $etablissement['etablissement_id'];
            if ($etablissement_id == null) {
                $etab = EtablissementClient::getInstance()->find(EtablissementClient::getInstance()->findByRaisonSociale($etablissement['raison_sociale']));
                $etab->ppm = $etablissement['ppm'];
                $etablissement_id = $etab->_id;
                $etab->save();
            }
            $etablissement_source->addLiaison("BAILLEUR", EtablissementClient::getInstance()->find($etablissement_id), true);
        }
        $etablissement_source->save();
    }
}
