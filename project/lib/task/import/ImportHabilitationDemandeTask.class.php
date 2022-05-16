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

       $batch_max=50;
       $i = 0;
        foreach(file($arguments['file']) as $line) {
            $line = str_replace("\n", "", $line);

            if(preg_match("/^#/", $line)) {

                continue;
            }

            $data = str_getcsv($line, ';');
            $identifiant = sprintf("%06d01", $data[5]);
            $etatHabilitation = strtolower(trim($data[24]));
            $produit = $this->convertProduit($data[0]);
            $dateCompletude = $this->convertDateFr($data[1]);
            $dateEnregistrement = $this->convertDateFr($data[2]);
            $dateTransmissionOI = $this->convertDateFr($data[15]);
            $dateDecision = $this->convertDateFr($data[25]);
            $typeDemande = HabilitationClient::DEMANDE_HABILITATION;

            if($etatHabilitation == "retrait") {
                $typeDemande = HabilitationClient::DEMANDE_RETRAIT;
            }
            if($dateDecision && $dateDecision < $dateEnregistrement) {
                $dateEnregistrement = $dateDecision;
            }
            if($dateDecision && $dateDecision < $dateCompletude) {
                $dateCompletude = $dateDecision;
            }
            if($dateEnregistrement < $dateCompletude) {
                $dateEnregistrement = $dateCompletude;
            }

            $activites = array();
            if($data[19]) {
                $activites[] = HabilitationClient::ACTIVITE_PRODUCTEUR;
            }
            if($data[20]) {
                $activites[] = HabilitationClient::ACTIVITE_VINIFICATEUR;
            }
            if($data[21]) {
                $activites[] = HabilitationClient::ACTIVITE_CONDITIONNEUR;
            }
            if($data[22]) {
                $activites[] = HabilitationClient::ACTIVITE_VRAC;
            }
            if($data[23]) {
                $activites[] = HabilitationClient::ACTIVITE_VENTE_A_LA_TIREUSE;
            }
            $commentaire = $data[26];
            $pourqui = $data[13];
            if(!EtablissementClient::getInstance()->find("ETABLISSEMENT-".$identifiant, acCouchdbClient::HYDRATE_JSON)) {
                echo "ERROR;Établissement introuvable;$line\n";
                continue;
            }
            if(!$dateCompletude) {
                echo "ERROR;Date de complétude;$line\n";
                continue;
            }
            if(!$dateDecision && $etatHabilitation) {
                echo "ERROR;Date de décision;$line\n";
                continue;
            }
            if(!$produit) {
                echo "ERROR;Produit;$line\n";
                continue;
            }
            if($dateDecision && !in_array($etatHabilitation, array("habilité", "refus", "retrait"))) {
                echo "ERROR;Statut non connu $etatHabilitation;$line\n";
                continue;
            }

            try {
                $demande = HabilitationClient::getInstance()->createDemandeAndSave($identifiant, HabilitationClient::CHAIS_PRINCIPAL, $typeDemande, $produit, $activites, "COMPLET", $dateCompletude, $commentaire, "import", false);
                $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateEnregistrement, "ENREGISTREMENT", null, "import", false);

                if($pourqui) {
                    $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateEnregistrement, "TRANSMIS_".$pourqui, null, "import", false);
                }

                if($pourqui && !preg_match("/^OI/", $pourqui) && $dateTransmissionOI) {
                    $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateTransmissionOI, "TRANSMIS_".$pourqui, null, "import", false);
                }

                $organismeValidateur = "INAO";
                if($pourqui == "CERTIPAQ") {
                    $organismeValidateur = "CERTIPAQ";
                }
                if($pourqui == "ODG") {
                    $organismeValidateur = "ODG";
                }

                if($dateDecision && in_array($etatHabilitation, array("habilité", "retrait"))) {
                    $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateDecision, "VALIDE_".$organismeValidateur, null, "import", false);
                    $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateDecision, "VALIDE", $commentaire, "import", false);
                } elseif($dateDecision && $etatHabilitation == "refus") {
                    $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateDecision, "REFUSE_". $organismeValidateur, null, "import", false);
                    $demande = HabilitationClient::getInstance()->updateDemandeAndSave($identifiant, $demande->getKey(), $dateDecision, "REFUSE", $commentaire, "import", false);
                } elseif($dateDecision) {
                    echo "ERROR;Statut non connu $etatHabilitation;$line\n";
                    continue;
                }
            } catch(Exception $e) {
                echo "ERROR;".$e->getMessage().";$line\n";
               sleep(3);
                continue;
            }
           if($i > $batch_max) {
               $i = 0;
               sleep(3);
           }
           $i++;
        }
    }

    protected function convertProduit($produitLibelle) {
        if($produitLibelle == "SAINT PERAY") {
            $produitLibelle = "Saint Peray Tranquilles";
        }

        foreach(HabilitationClient::getInstance()->getProduitsConfig(ConfigurationClient::getCurrent()) as $produit) {
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
