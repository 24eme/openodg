<?php

class ImportParcellairesDouaniersTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellaires-douaniers';
        $this->briefDescription = "Import des parcellaires douaniers";
        $this->detailedDescription = <<<EOF
The [import:parcellaires-douaniers|INFO] Importe le parcellaires de tous les ressortissants depuis prodouane.
Call it with:

  [php symfony import:parcellaires-douaniers|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();


        $items = EtablissementAllView::getInstance()->findByInterproStatutAndFamilleVIEW('INTERPRO-declaration', 'ACTIF', null);
		$i=0;
		$nb = count($items);
        $parcellaire_client = ParcellaireClient::getInstance();
        foreach ($items as $item) {
        	$i++;
        	echo "PROCESSUS;".$i.'/'.$nb.' => '.floor($i / $nb * 100)."\n";

        	if ($etablissement = EtablissementClient::getInstance()->find($item->id)) {
        	    try {
        	        $errors = [];
        	        $errors['csv'] =  '';
        	        $errors['json'] = '';
        	        $msg = '';
        	        if (!$parcellaire_client->saveParcellaire($etablissement, $errors)) {
        	            $msg = $errors['csv'].'\n'.$errors['json'];
        	        }
        	    } catch (Exception $e) {
        	        $msg = $e->getMessage();
        	    }
        	    if (!empty($msg)) {
        	        echo sprintf("ERROR;%s\n", $msg);
        	        continue;
        	    } else {
        	        echo sprintf("SUCCESS;Document douanier importé;%s\n", $item->id);
        	    }

        	} else {
        		echo sprintf("ERROR;Etablissement non trouvé %s\n", $item->id);
        	}
        }
    }
}
