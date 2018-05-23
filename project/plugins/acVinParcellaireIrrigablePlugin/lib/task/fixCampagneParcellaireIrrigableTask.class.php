<?php

class fixCampagneParcellaireIrrigableTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('parcellaireIrrigableId', sfCommandArgument::REQUIRED, "Id du parcellaire irrigable"),
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, "campagne")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'campagne-parcellaire-irrigable';
        $this->briefDescription = "Corrige la campagne du parcellaire irrigable avec la campagne passée en parametre";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $id = $arguments['parcellaireIrrigableId'];
        $campagne = $arguments['campagne'];

        $p = ParcellaireIrrigableClient::getInstance()->find($id);

        if(!$p){
            throw new sfException("Le parcellaire irrigable d'id $id n'est pas trouvé dans la base", 1);
        }
        if(!preg_match("/^[0-9]{4}$/",$campagne)){
            throw new sfException("La campagne $campagne n'a pas le bon format : format = AAAA", 1);
        }

        $reelParcellaireIrrigable = clone $p;

        $reelParcellaireIrrigable->campagne = $campagne;
        $reelParcellaireIrrigable->set('_id', ParcellaireIrrigableClient::TYPE_COUCHDB.'-'.$reelParcellaireIrrigable->identifiant.'-'.$reelParcellaireIrrigable->campagne);
        $reelParcellaireIrrigable->add('_rev',null);

        $p->delete();

        $reelParcellaireIrrigable->save();
        echo "Le parcellaire irrigable sauvé est : $reelParcellaireIrrigable->_id \n";
    }
}
