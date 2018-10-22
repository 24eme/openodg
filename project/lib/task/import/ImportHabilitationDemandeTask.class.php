<?php

class ImportHabilitationDemande extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'habilitation-demande';
        $this->briefDescription = "Import des demandes d'habilitation";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $doc = null;
        $object = null;
        foreach(file($arguments['file']) as $line) {
            $line = str_replace("\n", "", $line);

            if(preg_match("/^#/", $line)) {

                continue;
            }

            $data = str_getcsv($line, ';');
            $identifiant = $data[2]."01";
            $etatHabilitation = $data[25];
            $produit = $this->convertProduit($data[3]);
            $dateDepot = $this->convertDateFr($data[13]);
            $dateCompletude = $this->convertDateFr($data[14]);
            $dateEnregistrement = $this->convertDateFr($data[19]);
            $dateTransmissionOI = $this->convertDateFr($data[22]);
            if($dateTransmissionOI == "SVA") {
                $dateTransmissionOI = $dateEnregistrement;
            }
            $dateDecision = $this->convertDateFr($data[26]);
            $activites = array();
            if($data[27]) {
                $activites[] = HabilitationClient::ACTIVITE_PRODUCTEUR;
            }
            if($data[28]) {
                $activites[] = HabilitationClient::ACTIVITE_VINIFICATEUR;
            }
            if($data[29]) {
                $activites[] = HabilitationClient::ACTIVITE_CONDITIONNEUR;
            }
            if($data[30]) {
                $activites[] = HabilitationClient::ACTIVITE_VRAC;
            }
            if($data[31]) {
                $activites[] = HabilitationClient::ACTIVITE_VENTE_A_LA_TIREUSE;
            }
            $commentaire = $data[4];
            $pourqui = str_replace(array("CI_SYND", "CPAQ"), array("CI", "CERTIPAQ"), $data[20]);
            print_r($data);
            var_dump($identifiant);
            var_dump($produit);
            var_dump($activites);
            var_dump($dateDepot);
            var_dump($commentaire);
            var_dump($pourqui);
            $demande = HabilitationClient::getInstance()->createDemandeAndSave($identifiant, "HABILITATION", $produit, $activites, "DEPOT", $dateDepot, $commentaire, "import", false);
            $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateCompletude, "COMPLET", null, "import", false);
            $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateEnregistrement, "ENREGISTREMENT", null, "import", false);
            if($pourqui && $dateTransmissionOI) {
                $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateTransmissionOI, "TRANSMIS_".$pourqui, null, "import", false);
            }

            $organismeValidateur = "INAO";
            if($pourqui == "CERTIPAQ") {
                $organismeValidateur = "CERTIPAQ";
            }
            if($pourqui == "ODG") {
                $organismeValidateur = "ODG";
            }

            if($dateDecision && $etatHabilitation == "habilité" && in_array($pourqui, array("CERTIPAQ"))) {
                $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateDecision, "VALIDE_".$organismeValidateur, null, "import", false);
                $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateDecision, "VALIDE", null, "import", false);
            }

            if($dateDecision && $etatHabilitation == "refus" && in_array($pourqui, array("OIVR", "CI"))) {
                $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateDecision, "REFUSE_". $organismeValidateur, null, "import", false);
                $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateDecision, "REFUSE", null, "import", false);
            }

        }
    }

    protected function convertProduit($produitLibelle) {
        if($produitLibelle == "SAINT PERAY") {
            $produitLibelle = "Saint Peray Tranquilles";
        }
        foreach(ConfigurationClient::getCurrent()->getProduitsCahierDesCharges() as $produit) {
            if(KeyInflector::slugify($produitLibelle) == KeyInflector::slugify($produit->getLibelleComplet())) {

                return $produit->getHash();
            }
        }

        return $produitLibelle;
    }

    protected function convertDateFr($dateFr) {

        return preg_replace("|^([0-9]+)/([0-9]+)/([0-9]+)$|", '\3-\2-\1', $dateFr);
    }
}
