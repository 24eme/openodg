<?php

class exportEtablissementsCsvTask extends sfBaseTask
{

    public static $statuts_libelles_export = array( HabilitationClient::STATUT_DEMANDE_HABILITATION => "Demande d'habilitation",
                                             HabilitationClient::STATUT_ATTENTE_HABILITATION => "En attente d'habilitation",
                                             HabilitationClient::STATUT_DEMANDE_RETRAIT => "Demande de retrait",
                                             HabilitationClient::STATUT_HABILITE => "Habilité",
                                             HabilitationClient::STATUT_SUSPENDU => "Suspension d’habilitation",
                                             HabilitationClient::STATUT_REFUS => "Refus d’habilitation",
                                             HabilitationClient::STATUT_ANNULE => "Annulé",
                                             HabilitationClient::STATUT_RETRAIT => "Retrait d’habilitation",
                                             HabilitationClient::STATUT_ARCHIVE => "Archivé");

    protected function configure()
    {
        $this->addArguments(array(
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'export';
        $this->name = 'etablissements-csv';
        $this->briefDescription = "Export csv des établissements";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $results = EtablissementClient::getInstance()->findAll();

        echo "Login,Titre,Raison sociale,Adresse,Adresse 2,Adresse 3,Code postal,Commune,CVI,SIRET,Téléphone bureau,Fax,Téléphone mobile,Email,Activité,Réception ODG,Enresgistrement ODG,Transmission AVPI,Date Habilitation,Date Archivage,Observation,Etat,IR,Ordre,Zone,Code comptable,Famille,Date de dernière modification,Statut,PPM,Identifiant etablissement\n";

       $cpt = 0;
        foreach($results->rows as $row) {
           $cpt++;
            if($cpt > 250) {
                sleep(3);
                $cpt = 0;
            }
            $etablissement = EtablissementClient::getInstance()->find($row->id, acCouchdbClient::HYDRATE_JSON);
            $societe = SocieteClient::getInstance()->find($etablissement->id_societe, acCouchdbClient::HYDRATE_JSON);
            $compte = CompteClient::getInstance()->find($etablissement->compte, acCouchdbClient::HYDRATE_JSON);
            $habilitation = HabilitationClient::getInstance()->getLastHabilitation($etablissement->identifiant, null, acCouchdbClient::HYDRATE_JSON);

            $habilitationActivites = '';
            if (isset($compte->tags->activite)) {
                $habilitationActivites = join('|', $compte->tags->activite);
            }

            $habilitationStatut = '';
            if (isset($compte->tags->statuts)) {
                $habilitationStatut = join('|', $compte->tags->statuts);
            }

            $ordre = null;

            if($etablissement->region && $etablissement->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR) {
                $ordre = 'CP ';
            }
            if($etablissement->region && $etablissement->famille == EtablissementFamilles::FAMILLE_COOPERATIVE) {
                $ordre = 'CC ';
            }
            if($etablissement->region && $etablissement->famille == EtablissementFamilles::FAMILLE_NEGOCIANT) {
                $ordre = 'N';
            }
            if($etablissement->region) {
                $ordre .= substr($etablissement->region, -2);
            }

            $extractIntitule = Etablissement::extractIntitule($etablissement->raison_sociale);
            $intitule = $extractIntitule[0];
            $raisonSociale = $extractIntitule[1];

            $adresses_complementaires = explode(' − ', str_replace(array('"',','),array('',''), $etablissement->adresse_complementaire));
            $adresse_complementaire = array_shift($adresses_complementaires);

            echo
            $societe->identifiant.",".
            $intitule.",".
            $this->protectIso($raisonSociale).",".
            str_replace(array('"',',', ';'), array('','', ''), $etablissement->adresse).",".
            str_replace(array('"',',', ';'), array('','', ''), $adresse_complementaire).",".
            implode(' − ', $adresses_complementaires).",".
            $etablissement->code_postal.",".
            $this->protectIso($etablissement->commune).",".
            $etablissement->cvi.",".
            '"'.$etablissement->siret.'",'.
            $etablissement->telephone_bureau.",".
            $etablissement->fax.",".
            $etablissement->telephone_mobile.",".
            '"'.$etablissement->email.'",'.
            $habilitationActivites.",". // Activité habilitation
            ','. //Reception ODG
            ','. //Enregistrement ODG
            ','. //Transmission AVPI
            ','. //Date Habilitation
            ','. //date archivage
            '"'.str_replace('"', "''", str_replace(array(',', ';'), array(' / ', ' / '), $this->protectIso($etablissement->commentaire))).'",'.
            $habilitationStatut.",". // Etat
            "Faux,", //demande AVPI
            $ordre.",". // Ordre
            str_replace("_", " ", preg_replace("/_[0-9]+$/", "", $etablissement->region)).",". // Region
            $societe->code_comptable_client.",".
            $etablissement->famille.",".
            $compte->date_modification.",".
            $etablissement->statut.",".
            $etablissement->ppm.",\"".
            $etablissement->identifiant."\",".
            "\n";
        }
    }

    public function protectIso($str){
        return str_replace(array('œ',',',"\n"),array('','',''),$str);
    }
}
