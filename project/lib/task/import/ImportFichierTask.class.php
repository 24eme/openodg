<?php

class ImportFichierTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, "Identifiant etablissment"),
        	new sfCommandArgument('fichier', sfCommandArgument::REQUIRED, "Chemin du fichier"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            
        	new sfCommandOption('libelle', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', null),
        	new sfCommandOption('visibilite', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', 1),
        	new sfCommandOption('papier', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', false),
        	new sfCommandOption('date_depot', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', null),
        	new sfCommandOption('type', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', null),
        	new sfCommandOption('annee', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', null),
        	new sfCommandOption('lien_symbolique', null, sfCommandOption::PARAMETER_OPTIONAL, 'Libelle fichier', false),
        ));

        $this->namespace = 'import';
        $this->name = 'fichier';
        $this->briefDescription = "Importe de fichier";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $file = $arguments['fichier'];
        
	   	if (!file_exists($file) || !is_file($file)) {
	    	echo sprintf("ERROR;Fichier introuvable ou non valide %s\n", $file);
	        return;
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

        if ($options['lien_symbolique']) {
        	if (!$options['type'] || !$options['annee']) {
        		echo sprintf("ERROR;Type et année obligatoire pour la création du lien symbolique\n");
        		return;
        	}
        	if (!preg_match('/^[0-9]{4}$/', $options['annee'])) {
        		echo sprintf("ERROR;Format année (Y) non valide %s\n", $options['annee']);
        		return;
        	}
        	$ls = LienSymboliqueClient::getInstance()->findByArgs($options['type'], $etablissement->identifiant,  $options['annee']);
        	$fichier = ($ls)? $ls->getFichierObject() : null; 
        	if (!$fichier) {
        		$fichier = FichierClient::getInstance()->createDoc($etablissement->identifiant, $options['papier']);
        	}
        	
        } else {
        	$fichier = FichierClient::getInstance()->createDoc($etablissement->identifiant, $options['papier']);
        }
        if ($fichier->isNew() && $options['libelle']) {
        	$fichier->setLibelle($options['libelle']);
        }
        if ($fichier->isNew() && $options['date_depot']) {
        	$fichier->setDateDepot($options['date_depot']);
        }
        if ($fichier->isNew() && $options['visibilite']) {
        	$fichier->setVisibilite($options['visibilite']);
        }
        try {
        	if ($fichier->isNew()) {
        		$fichier->save();
        	}
        	$fichier->storeFichier($file);
        	$fichier->save();
        	if ($options['lien_symbolique'] && !$ls) {
        		$lienSymbolique = LienSymboliqueClient::getInstance()->createDoc($options['type'], $etablissement->identifiant,  $options['annee'], $fichier->_id);
        		$lienSymbolique->save();
        	}
        } catch (Exception $e) {
        	echo sprintf("ERROR;%s\n",$e->getMessage());
        	return;
        }
        echo sprintf("SUCCESS;Fichier importé;%s\n", $fichier->_id);
    }
}
