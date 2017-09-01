<?php

class ImportFichierTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, "Identifiant etablissment"),
        	new sfCommandArgument('fichiers', sfCommandArgument::REQUIRED, "Chemin des fichiers, séparateur : |"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            
        	new sfCommandOption('libelle', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', null),
        	new sfCommandOption('visibilite', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', 1),
        	new sfCommandOption('papier', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', false),
        	new sfCommandOption('date_depot', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', null),
        ));

        $this->namespace = 'import';
        $this->name = 'fichier';
        $this->briefDescription = "Importe des fichiers";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        
        $files = explode('|', $arguments['fichiers']);
        
        foreach ($files as $file) {
	        if (!file_exists($file) || !is_file($file)) {
	        	echo sprintf("ERROR;Fichier introuvable ou non valide %s\n", $file);
	        	return;
	        }
        }
        
        $etablissement = EtablissementClient::getInstance()->findByIdentifiant($arguments['identifiant']);
        if (!$etablissement) {
        	$etablissement = EtablissementClient::getInstance()->findByCvi($arguments['identifiant']);
        }
        
        if(!$etablissement) {
            echo sprintf("ERROR;Etablissement introuvable %s\n", $arguments['identifiant']);
            return;
        }
        if ($options['date_depot'] && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $options['date_depot'])) {
        	echo sprintf("ERROR;Format date (Y-m-d) non valide %s\n", $options['date_depot']);
        	return;
        }
        
        $fichier = FichierClient::getInstance()->createDoc($arguments['identifiant'], $options['papier']);
        if ($options['libelle']) {
        	$fichier->setLibelle($options['libelle']);
        }
        if ($options['date_depot']) {
        	$fichier->setDateDepot($options['date_depot']);
        }
        if ($options['visibilite']) {
        	$fichier->setVisibilite($options['visibilite']);
        }
        try {
        	$fichier->save();
        	foreach ($files as $file) {
        		$fichier->storeFichier($file);
        	}
        	$fichier->save();
        } catch (Exception $e) {
        	echo sprintf("ERROR;%s\n",$e->getMessage());
        	return;
        }
        echo sprintf("SUCCESS;Fichier importé;%s\n", $fichier->_id);
    }
}
