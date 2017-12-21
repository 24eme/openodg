<?php

class CompteExportCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(

        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'compte';
        $this->name = 'export-csv';
        $this->briefDescription = "";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $comptes = CompteAllView::getInstance()->findByInterproVIEW('INTERPRO-declaration');

        echo "Date de modifications;Identifiant / Login;Nom / Raison sociale;Adresse;Adresse complémentaire;Code postal;Commune;Code INSEE;Téléphone bureau;Téléphone mobile;Téléphone perso;Fax;Email;SIRET;CVI;Statut;Id du doc\n";

        foreach($comptes as $row) {
            $compte = CompteClient::getInstance()->find($row->id, acCouchdbClient::HYDRATE_JSON);

            if(!$compte->mot_de_passe) {
                continue;
            }

            $login = preg_replace("/^([0-9]{6})([0-9]+)$/", '\1', $compte->identifiant);

            if(isset($compte->login) && $compte->login) {
                $login = $compte->login;
            }

            $societe = SocieteClient::getInstance()->find($compte->id_societe, acCouchdbClient::HYDRATE_JSON);
            $date_modification = $societe->date_modification;

            if(isset($compte->date_modification) && $compte->date_modification) {
                $date_modification = $compte->date_modification;
            }

            echo $date_modification.";".$login.";\"".str_replace('"', '\"', $compte->nom_a_afficher)."\";\"".str_replace('"', '\"', $compte->adresse)."\";\"".str_replace('"', '\"',$compte->adresse_complementaire)."\";".$compte->code_postal.";\"".str_replace('"', '\"',$compte->commune)."\";".$compte->insee.";".$compte->telephone_bureau.";".$compte->telephone_mobile.";".$compte->telephone_perso.";".$compte->fax.";".$compte->email.";".$societe->siret.";".$compte->etablissement_informations->cvi.";".$compte->statut.";".$compte->_id."\n";
        }

    }


}
