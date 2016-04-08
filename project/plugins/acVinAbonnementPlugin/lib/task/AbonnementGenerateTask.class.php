<?php

class AbonnementGenerateTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('compte_id', sfCommandArgument::REQUIRED, "ID du compte"),
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, "Campagne de génération de l'abonnement"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'abonnement';
        $this->name = 'generate';
        $this->briefDescription = 'Génération des abonnements';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $campagne = $arguments['campagne'];
        $compte = CompteClient::getInstance()->find($arguments['compte_id'], acCouchdbClient::HYDRATE_JSON);

        if(!$compte) {

            return;
        }

        if($compte->statut != 'ACTIF') {

            return;
        }

        if(AbonnementClient::getInstance()->findByIdentifiantAndDate($compte->identifiant, $this->getDateDebut($campagne), $this->getDateFin($campagne), acCouchdbClient::HYDRATE_JSON)) {

            //return;
        }

        $prevAbo = AbonnementClient::getInstance()->findByIdentifiantAndDate($compte->identifiant, $this->getDateDebut($campagne-1), $this->getDateFin($campagne-1), acCouchdbClient::HYDRATE_JSON);

        $isCompteAbonneRevue = false;
        foreach($compte->infos->manuels as $tag) {
            if($tag == "Abonné revue")  {
                $isCompteAbonneRevue = true;
                break;
            }
        }

        if(!$isCompteAbonneRevue && $prevAbo) {
            echo "WARNING Plus abonné : $compte->identifiant ($compte->nom_a_afficher)\n";
        }

        if(!$isCompteAbonneRevue) {

            return;
        }

        if(!$prevAbo && $compte->type_compte != CompteClient::TYPE_COMPTE_ETABLISSEMENT) {
            echo "ERROR Pas d'abonnement précédent $compte->identifiant ($compte->nom_a_afficher)\n";

            return;
        }

        $abo = AbonnementClient::getInstance()->findOrCreateDoc($compte->identifiant, $this->getDateDebut($campagne), $this->getDateFin($campagne));

        $abo->mouvements = array();
        $abo->generateMouvements();

        if($compte->type_compte == CompteClient::TYPE_COMPTE_ETABLISSEMENT) {
            $abo->tarif = AbonnementClient::TARIF_MEMBRE;
        } else {
            $abo->tarif = $prevAbo->tarif;
        }

        if($prevAbo && $abo->tarif != $prevAbo->tarif) {
            echo "ERROR Le tarif $abo->tarif de l'abonnement diffère de celui de l'année dernière $prevAbo->tarif $compte->identifiant ($compte->nom_a_afficher)\n";

            return;
        }

        if(in_array($abo->tarif, array(AbonnementClient::TARIF_GRATUIT, AbonnementClient::TARIF_PLEIN, AbonnementClient::TARIF_ETRANGER))) {
            $abo->facturerMouvements();
        }

        if($abo->tarif != AbonnementClient::TARIF_MEMBRE) {

            return;
        }

        $abo->save();

        echo "SUCCESS Création de l'abonnement ".$abo->_id."\n";
    }

    protected function getDateDebut($campagne) {

        return $campagne."-01-01";
    }

    protected function getDateFin($campagne) {

        return $campagne."-12-31";
    }

}
