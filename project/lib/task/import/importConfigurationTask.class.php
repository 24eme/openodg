<?php

class importConfigurationTask extends sfBaseTask
{

    protected $cepage_order = array("CH", "SY", "AU", "PB", "PI", "ED", "RI", "PG", "MU", "MO", "GW");
    
    protected function configure()
    {
        // // add your own arguments here
        $this->addArguments(array(
           new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, 'Campagne'),
         ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            // add your own options here
            new sfCommandOption('import', null, sfCommandOption::PARAMETER_REQUIRED, 'import type [couchdb|stdout]', 'couchdb'),
            new sfCommandOption('removedb', null, sfCommandOption::PARAMETER_REQUIRED, '= yes if remove the db debore import [yes|no]', 'no'),
        ));

        $this->namespace = 'import';
        $this->name = 'Configuration';
        $this->briefDescription = 'import configuration';
        $this->detailedDescription = <<<EOF
The [import|INFO] task does things.
Call it with:

  [php symfony import|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        ini_set('memory_limit', '512M');
        set_time_limit('3600');
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        if ($options['removedb'] == 'yes' && $options['import'] == 'couchdb') {
            if (acCouchdbManager::getClient()->databaseExists()) {
                acCouchdbManager::getClient()->deleteDatabase();
            }
            acCouchdbManager::getClient()->createDatabase();
        }
        
        /*
         * Parsing de la configuration Civa
         */
        $configurationJson = file_get_contents(sfConfig::get('sf_data_dir') . '/import/configuration/'.$arguments['campagne'].'.json');
        
        if (!$configurationJson) {
        	throw new sfCommandException("Le fichier de configuration n'est pas existant dans l'arborescence ".sfConfig::get('sf_data_dir') . '/import/configuration/');
        }
        $configurationJson = json_decode($configurationJson);

        unset($configurationJson->_rev);

        if(isset($configurationJson->recolte)) {
            $configurationJson->declaration = $configurationJson->recolte;
            unset($configurationJson->recolte);
        }
        
        $certifications = $configurationJson->declaration->certification;
        unset($configurationJson->declaration->certification);
        /*
         * Identification des appellations revendiquees
         */
        $configurationJson->declaration->certification = new stdClass();
        $configurationJson->declaration->certification->genre = new stdClass();

        $configurationJson->declaration->certification->genre->appellation_ALSACEBLANC = $certifications->genre->appellation_ALSACEBLANC;
        @$configurationJson->declaration->certification->genre->appellation_ALSACEBLANC->relations->lots = "appellation_ALSACE";

        $configurationJson->declaration->certification->genre->appellation_PINOTNOIR = $certifications->genre->appellation_PINOTNOIR;
        @$configurationJson->declaration->certification->genre->appellation_PINOTNOIR->relations->lots = "appellation_ALSACE";

        $configurationJson->declaration->certification->genre->appellation_PINOTNOIRROUGE = $certifications->genre->appellation_PINOTNOIRROUGE;
        @$configurationJson->declaration->certification->genre->appellation_PINOTNOIRROUGE->relations->lots = "appellation_ALSACE";

        $configurationJson->declaration->certification->genre->appellation_COMMUNALE = $certifications->genre->appellation_COMMUNALE;
        @$configurationJson->declaration->certification->genre->appellation_COMMUNALE->relations->lots = "appellation_ALSACE";
        foreach($configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention as $key_lieu => $lieu) {
            if(!preg_match('/^lieu/', $key_lieu) || $key_lieu == "lieu") {
                continue;
            }
            
            @$lieu->relations->lots = "lieu";
            @$lieu->relations->revendication = "lieu";
            foreach($lieu as $key_couleur => $couleur) {
                if(!preg_match('/^couleur/', $key_couleur) || $key_couleur == "couleur") {
                    continue;
                }
                @$couleur->relations->lots = "couleur";
            }
        }
        $configurationJson->declaration->certification->genre->appellation_LIEUDIT = $certifications->genre->appellation_LIEUDIT;
        @$configurationJson->declaration->certification->genre->appellation_LIEUDIT->relations->lots = "appellation_ALSACE";
        @$configurationJson->declaration->certification->genre->appellation_LIEUDIT->mention->lieu->couleurBlanc->relations->lots = "couleur";
        @$configurationJson->declaration->certification->genre->appellation_LIEUDIT->mention->lieu->couleurRouge->relations->lots = "couleur";

        $configurationJson->declaration->certification->genre->appellation_GRDCRU = $certifications->genre->appellation_GRDCRU;

        $configurationJson->declaration->certification->genre->appellation_CREMANT = $this->getConfigurationCremant($certifications->genre->appellation_CREMANT);
        $grdCruCepages = $this->getCepages($configurationJson->declaration->certification->genre->appellation_GRDCRU);
        $configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu = new stdClass();
        $configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu->couleur = $grdCruCepages;
        foreach($configurationJson->declaration->certification->genre->appellation_GRDCRU->mention as $key_lieu => $lieu) {
            if(!preg_match('/^lieu/', $key_lieu) || $key_lieu == "lieu") {
                continue;
            }
            @$lieu->relations->revendication = "lieu";
        }

        $communaleBlancCepages = $this->getCepages($configurationJson->declaration->certification->genre->appellation_COMMUNALE, 'couleurBlanc');
        $communaleRougeCepages = $this->getCepages($configurationJson->declaration->certification->genre->appellation_COMMUNALE, 'couleurRouge');
        $configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu = new stdClass();
        $configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurBlanc = $communaleBlancCepages;
        $configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurBlanc->libelle = 'Blanc';
        @$configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurBlanc->relations->lots = "couleur";
        $configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurRouge = $communaleRougeCepages;
        $configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurRouge->libelle = 'Rouge';
        @$configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurRouge->relations->lots = "couleur";
        
        /*
         * Modification des libelles pour le Pinot
         */
        $configurationJson->declaration->certification->genre->appellation_PINOTNOIR->libelle = 'AOC Alsace Pinot Noir Rosé';    
        $configurationJson->declaration->certification->genre->appellation_PINOTNOIRROUGE->libelle = 'AOC Alsace Pinot Noir Rouge';

         /*
         * Modification des libelles pour l'assemblage
         */
        $configurationJson->declaration->certification->genre->appellation_ALSACEBLANC->mention->lieu->couleur->cepage_ED->libelle = 'Assemblage/Edelzwicker';
        $configurationJson->declaration->certification->genre->appellation_LIEUDIT->mention->lieu->couleurBlanc->cepage_ED->libelle = 'Assemblage/Edelzwicker';
//        $configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu02->couleur->cepage_ED->libelle = 'Assemblage Edel';
//        $configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu51->couleur->cepage_ED->libelle = 'Assemblage Edel'; 
//        $configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu->couleur->cepage_ED->libelle = 'Assemblage Edel';        
        /*
         * On ajoute l'appellation Alsace pour la gestion des lots
         */
        $alsaceCepages = $this->getAlsaceCepages($configurationJson->declaration->certification->genre);
        $configurationJson->declaration->certification->genre->appellation_ALSACE = new stdClass();
        $configurationJson->declaration->certification->genre->appellation_ALSACE->appellation = 'ALSACE';
        $configurationJson->declaration->certification->genre->appellation_ALSACE->libelle = 'AOC Alsace';
        @$configurationJson->declaration->certification->genre->appellation_ALSACE->mention->lieu->couleur = $alsaceCepages;

        /*
         * Identification des produits (niveau couleur) de la DRev
         */
        @$configurationJson->declaration->certification->genre->appellation_ALSACE->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION} = 1;
        @$configurationJson->declaration->certification->genre->appellation_ALSACE->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE} = 1;
        foreach($configurationJson->declaration->certification->genre->appellation_GRDCRU->mention as $key_lieu => $lieu) {
            if($key_lieu == "lieu") {
                @$lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE} = 1;
            }
            if(!preg_match('/^lieu/', $key_lieu) || $key_lieu == "lieu") {
                continue;
            }
            @$lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION} = 1;
        }
        foreach($configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention as $key_lieu => $lieu) {
            if($key_lieu == "lieu") {
                @$lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE} = 1;
            }
            if(!preg_match('/^lieu/', $key_lieu) || $key_lieu == "lieu") {
                continue;
            }

            @$lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION} = 1;
        }

         /*
         * Identification des produits (niveau couleur) pour les lots
         */
        @$configurationJson->declaration->certification->genre->appellation_ALSACEBLANC->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS} = 1;
        @$configurationJson->declaration->certification->genre->appellation_PINOTNOIR->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS} = 1;
        @$configurationJson->declaration->certification->genre->appellation_PINOTNOIRROUGE->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS} = 1;
        @$configurationJson->declaration->certification->genre->appellation_COMMUNALE->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS} = 1;
        @$configurationJson->declaration->certification->genre->appellation_LIEUDIT->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS} = 1;
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS} = 1;
        @$configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS} = 1;

    	if ($options['import'] == 'couchdb') {
    		
    		if ($doc = acCouchdbManager::getClient()->find($configurationJson->_id, acCouchdbClient::HYDRATE_JSON)) {
            	acCouchdbManager::getClient()->deleteDoc($doc);
            }
            $doc = acCouchdbManager::getClient()->createDocumentFromData($configurationJson);
        	$doc->save();
        	$this->logSection('configuration', 'Configuration importée avec succès');
        } else {
            echo '{"docs":';
            echo json_encode($configurationJson);
            echo '}';
            echo "\n";
        }

    }
    
    protected function getAlsaceCepages($genreNode)
    {
    	$cepages = array();
    	$appellations = array(
    		'appellation_ALSACEBLANC', 
    		'appellation_PINOTNOIR', 
    		'appellation_PINOTNOIRROUGE',
    		'appellation_COMMUNALE',
    		'appellation_LIEUDIT'
    	);
    	foreach ($appellations as $appellation) {
    		$appellationCepages = VarManipulator::objectToArray($this->getCepages($genreNode->{$appellation}));
    		foreach ($appellationCepages as $cep => $appellationCepage) {
    			if (!isset($cepages[$cep])) {
    				$cepages[$cep] = $appellationCepage;
    			}
    		}
    	}
    	return VarManipulator::arrayToObject($cepages);
    }
    
    protected function getConfigurationCremant($appellations)
    {
        unset($appellations->mention->lieu->couleur->cepage_BL);
        unset($appellations->mention->lieu->couleur->cepage_RS);
        $cepageRB = $appellations->mention->lieu->couleur->cepage_RB;
        unset($appellations->mention->lieu->couleur->cepage_RB);

        $appellations->mention->lieu->couleur->cepage_BLRS = new stdClass();
    	$appellations->mention->lieu->couleur->cepage_BLRS->libelle = "Blanc + Rosé";
    	$appellations->mention->lieu->couleur->cepage_BLRS->rendement = null;
        $appellations->mention->lieu->couleur->cepage_BLRS->no_dr = 1;
    	$appellations->mention->lieu->couleur->cepage_BLRS->auto_drev = 1;
        $appellations->mention->lieu->couleur->cepage_RB = $cepageRB;
    	
    	return $appellations;
    }
    
    protected function getCepages($appellation, $noeudCouleur = 'couleur') 
    {
    	$cepages = new stdClass();
    	foreach ($appellation as $m => $mention) {
    		if (preg_match('/^mention/', $m)) {
    			foreach ($mention as $l => $lieu) {
    				if (preg_match('/^lieu/', $l)) {
    					foreach ($lieu as $co => $couleur) {
    						if (preg_match('/^'.$noeudCouleur.'/', $co)) {
    							foreach ($couleur as $c => $cepage) {
    								if (preg_match('/^cepage/', $c)) {
    									if (!isset($cepages->{$c})) {
    										$cepages->{$c} = $cepage;
    									}
    								}		
    							}		
    						}
    					}	
    				}	
    			}		
    		}
    	}
    	return $cepages;
    }

}