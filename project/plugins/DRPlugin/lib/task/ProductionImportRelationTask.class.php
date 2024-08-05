<?php

class ProductionImportRelationTask extends sfBaseTask
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

        $this->namespace = 'production';
        $this->name = 'import-relation';
        $this->briefDescription = "Importe les relations à partir d'un document de production";
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
            $tiers = $declaration_production->getTiers(false, EtablissementClient::TYPE_LIAISON_NEGOCIANT_VINIFICATEUR);
            $this->setTiersRelation($etablissement_source, $tiers, EtablissementClient::TYPE_LIAISON_NEGOCIANT_VINIFICATEUR);
        }
        if ($declaration_production->hasApporteurs()) {
            $tiers = $declaration_production->getApporteurs(true);
            if ($declaration_production["type"] === "SV11") {
                $this->setTiersRelation($etablissement_source, $tiers, EtablissementClient::TYPE_LIAISON_COOPERATEUR);
            }
            if ($declaration_production["type"] === "SV12") {
                $this->setTiersRelation($etablissement_source, $tiers, EtablissementClient::TYPE_LIAISON_APPORTEUR_RAISIN);
            }
        }
    }

    public function setTiersRelation($etablissement_source, $tiers, $relation) {
        foreach ($tiers as $etablissement) {
            if(!$etablissement['etablissement']) {
                continue;
            }
            $etablissement_source->addLiaison($relation, EtablissementClient::getInstance()->find($etablissement['etablissement']->_id), true);
        }
        $etablissement_source->save();
    }


    public function getBailleurRelation($etablissement_source, $etablissements_bailleur) {
        foreach ($etablissements_bailleur as $etablissement) {
            $etablissement_id = $etablissement['etablissement_id'];
            if ($etablissement_id == null) {
                $etab_target = EtablissementClient::getInstance()->findByRaisonSociale($etablissement['raison_sociale']);
                if (!$etab_target) {
                    echo "Erreur bailleur non reconnu: [".$etablissement['raison_sociale']."] n'existe pas. PPM = [".$etablissement['ppm']."]".PHP_EOL;
                    continue;
                }
                $etab_target->ppm = $etablissement['ppm'];
                $etablissement_id = $etab_target->_id;
                $etab_target->save();
            }
            $etablissement_source->addLiaison("BAILLEUR", EtablissementClient::getInstance()->find($etablissement_id), true);
        }
        $etablissement_source->save();
    }
}
