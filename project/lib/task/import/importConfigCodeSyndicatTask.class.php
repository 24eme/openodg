<?php

class importConfigCodeSyndicatTask extends sfBaseTask {

    protected function configure() {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('file', null, sfCommandOption::PARAMETER_REQUIRED, 'fichier d\'import'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            // add your own options here
        ));

        $this->namespace = 'import';
        $this->name = 'ConfigCodeSyndicat';
        $this->briefDescription = 'import code syndicat';
        $this->detailedDescription = <<<EOF
The [import|INFO] task does things.
Call it with:

  [php symfony import|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        ini_set('memory_limit', '512M');
        set_time_limit('3600');
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $lines = file($arguments['file']);
        $assoc = array();
        foreach ($lines as $l) {
          $l = rtrim($l);
          $values = explode(';', $l);
          $assoc[$values[0]] = $values[1];
        }

        $conf = ConfigurationClient::getCurrent();
        foreach($conf->getProduits() as $p) {
            if (isset($assoc[$p->code_douane])) {
              $p->code_produit = $assoc[$p->code_douane];
              echo $p->getLibelleComplet()." => ".$p->code_produit."\n";
            }else{
              echo "RIEN TROUVÉ pour ".$p->getLibelleComplet()."\n";
            }
        }
        $conf->save();
        echo $conf->_id." sauvé\n";
    }

}
