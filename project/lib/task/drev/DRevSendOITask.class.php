<?php

class DRevSendOITask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('regions', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', false),
        ));

        $this->namespace = 'drev';
        $this->name = 'send-oi';
        $this->briefDescription = "Envoi des DRevs à l'organisme d'inspection";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $contextInstance = sfContext::createInstance($this->configuration);
        $regions = null;
        if($options['regions']){
          $regions = array_keys(sfConfig::get('app_oi_regions'));
        }

        $items = DrevAttenteOiView::getInstance()->getAll();
        foreach ($items as $item) {
        	$drev = DRevClient::getInstance()->find($item->id);
          if($regions){
              $regionSended = array();
              foreach ($regions as $region) {
                if(!count($drev->getProduits($region))){
                  continue;
                }
                try {
              		 $drevOi = new DRevOI($drev, $contextInstance,$region);
              		 $drevOi->send();
              		echo sprintf("SUCCESS;La DRev a bien été envoyée à l'organisme d'inspection;%s;%s\n", $drev->_id,$region);
                  $regionSended[] = $region;
              	} catch (sfException $e) {
              		echo sprintf("ERROR;L'envoi de la DRev à l'organisme d'inspection a échoué;%s;%s\n", $e->getMessage(),$region);
              	}
              }
              if(count($regionSended) > 0){
                $drev->add('envoi_oi', date('c'));
			          $drev->save();
                echo sprintf("SUCCESS;La %s a été envoyée à : %s\n", $drev->_id,implode(",",$regionSended));
              }
          }else{
            try {
          		$drevOi = new DRevOI($drev, $contextInstance);
          		$drevOi->send();
          		echo sprintf("SUCCESS;La DRev a bien été envoyée à l'organisme d'inspection;%s\n", $drev->_id);
          	} catch (sfException $e) {
          		echo sprintf("ERROR;L'envoi de la DRev à l'organisme d'inspection a échoué;%s\n", $e->getMessage());
          	}
          }
        }
    }
}
