<?php

class CompteTestsTask extends sfBaseTask
{

    protected function configure()
    {

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'compte';
        $this->name = 'tests';
        $this->briefDescription = 'Jeu de tests pour les comptes';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        
        for ($i=1; $i<10; $i++) {
        	$siret = "4444416040003".$i;
        	$compte = CompteClient::getInstance()->find("COMPTE-".$siret);
        	if (!$compte) {
        		$compte = new Compte();
        		$compte->_id = "COMPTE-".$siret;
        	}
	    	$compte->identifiant = "test".$i;
	      	$compte->type_compte = "PERSONNE";
	      	$compte->civilite = "M";
	      	$compte->nom = "Compte";
	      	$compte->prenom = "Test";
	      	$compte->nom_a_afficher = "Test Compte ".$i;
	      	$compte->adresse = "1 rue Garnier";
	      	$compte->code_postal = "92200";
	      	$compte->ville = "Neuilly Sur Seine";
	      	$compte->telephone_bureau = "0171113190";
	      	$compte->telephone_prive = "";
	      	$compte->telephone_mobile = "+33689285475";
	      	$compte->fax = "+33141434803";
	      	$compte->email = "contact@actualys.com";
	      	$compte->siret = $siret;
	      	$compte->cvi = ""; 
	      	$compte->etablissement = "";
        	if ($i%3 == 0) {
        		$compte->tags->attributs->add(null,"Dégustateur");
        		$compte->tags->attributs->add(null,"Prélèvement");
        	} else {
        		if ($i%2 == 0) {
        			$compte->tags->attributs->add(null,"Dégustateur");
        		} else {
        			$compte->tags->attributs->add(null,"Prélèvement");
        		}
        	}
        	if ($i%3 == 0) {
        		$compte->tags->manuels->add(null,"Hôtel");
        		$compte->tags->manuels->add(null,"Restaurant");
        	} else {
        		if ($i%2 == 0) {
        			$compte->tags->manuels->add(null,"Hôtel");
        		} else {
        			$compte->tags->manuels->add(null,"Restaurant");
        		}
        	}
        	if ($i%4 == 0) {
        		$compte->tags->produits->add(null,"AOC Alsace blanc");
        		$compte->tags->produits->add(null,"AOC Alsace Grands Crus");
        		$compte->tags->produits->add(null,"AOC Crémant d'Alsace");
        		$compte->tags->produits->add(null,"AOC Alsace Pinot Noir Rouge");
        	} else {
        		if ($i%3 == 0) {
	        		$compte->tags->produits->add(null,"AOC Alsace blanc");
	        		$compte->tags->produits->add(null,"AOC Crémant d'Alsace");
	        		$compte->tags->produits->add(null,"AOC Alsace Pinot Noir Rouge");
        		} else {
        			if ($i%2 == 0) {
        				$compte->tags->produits->add(null,"AOC Alsace blanc");
        				$compte->tags->produits->add(null,"AOC Alsace Pinot Noir Rouge");
        			} else {
        				$compte->tags->produits->add(null,"AOC Crémant d'Alsace");
        			}
        		}
        	}
        	try {
        		$compte->save();
        	}	catch (Exception $e) {
        		$this->logSection('compte', 'Erreur sauvegarde du compte test '.$siret, null, 'ERROR');
        		continue;
        	}
        	$this->logSection('compte', 'Compte test '.$siret.' sauvegarde avec succes');
        }
      

    }
}