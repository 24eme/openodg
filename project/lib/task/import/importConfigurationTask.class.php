<?php

class importConfigurationTask extends sfBaseTask {

    protected $cepage_order = array("CH", "SY", "AU", "PB", "PI", "ED", "RI", "PG", "MU", "MO", "GW");

    protected function configure() {
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

    protected function execute($arguments = array(), $options = array()) {
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
        $configurationJson = file_get_contents(sfConfig::get('sf_data_dir') . '/import/configuration/2014.json');

        if (!$configurationJson) {
            throw new sfCommandException("Le fichier de configuration n'est pas existant dans l'arborescence " . sfConfig::get('sf_data_dir') . '/import/configuration/');
        }
        $configurationJson = json_decode($configurationJson);

        unset($configurationJson->_rev);

        $configurationJson->campagne = $arguments['campagne'];
        $configurationJson->_id = "CONFIGURATION-" . $arguments['campagne'];

        if (isset($configurationJson->recolte)) {
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

        @$configurationJson->declaration->certification->genre->appellation_ALSACEBLANC->mention->lieu->couleur->cepage_MU->libelle = "Muscat";

        $configurationJson->declaration->certification->genre->appellation_PINOTNOIR = $certifications->genre->appellation_PINOTNOIR;
        @$configurationJson->declaration->certification->genre->appellation_PINOTNOIR->relations->lots = "appellation_ALSACE";

        $configurationJson->declaration->certification->genre->appellation_PINOTNOIRROUGE = $certifications->genre->appellation_PINOTNOIRROUGE;
        @$configurationJson->declaration->certification->genre->appellation_PINOTNOIRROUGE->relations->lots = "appellation_ALSACE";

        $configurationJson->declaration->certification->genre->appellation_COMMUNALE = $certifications->genre->appellation_COMMUNALE;
        @$configurationJson->declaration->certification->genre->appellation_COMMUNALE->relations->lots = "appellation_ALSACE";
        foreach ($configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention as $key_lieu => $lieu) {
            if (!preg_match('/^lieu/', $key_lieu) || $key_lieu == "lieu") {
                continue;
            }

            @$lieu->relations->lots = "lieu";
            @$lieu->relations->revendication = "lieu";
            foreach ($lieu as $key_couleur => $couleur) {
                if (!preg_match('/^couleur/', $key_couleur) || $key_couleur == "couleur") {
                    continue;
                }
                @$couleur->relations->lots = "couleur";
            }
        }
        $configurationJson->declaration->certification->genre->appellation_LIEUDIT = $certifications->genre->appellation_LIEUDIT;
        @$configurationJson->declaration->certification->genre->appellation_LIEUDIT->relations->lots = "appellation_ALSACE";
        @$configurationJson->declaration->certification->genre->appellation_LIEUDIT->mention->lieu->couleurBlanc->relations->lots = "couleur";
        @$configurationJson->declaration->certification->genre->appellation_LIEUDIT->mention->lieu->couleurRouge->relations->lots = "couleur";

        @$configurationJson->declaration->certification->genre->appellation_LIEUDIT->mention->lieu->couleurBlanc->cepage_MU->libelle = "Muscat";

        $configurationJson->declaration->certification->genre->appellation_GRDCRU = $certifications->genre->appellation_GRDCRU;

        $configurationJson->declaration->certification->genre->appellation_CREMANT = $this->getConfigurationCremant($certifications->genre->appellation_CREMANT);
        $grdCruCepages = $this->getCepages($configurationJson->declaration->certification->genre->appellation_GRDCRU);
        $configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu = new stdClass();
        $configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu->couleur = $grdCruCepages;
        foreach ($configurationJson->declaration->certification->genre->appellation_GRDCRU->mention as $key_lieu => $lieu) {
            if (!preg_match('/^lieu/', $key_lieu) || $key_lieu == "lieu") {
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

        @$configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieuKLEV->couleurBlanc->cepage_KL->libelle = "Savagnin Rose";
        @$configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieuKLEV->couleurBlanc->cepage_KL->libelle_long = "Savagnin Rose";

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


        @$configurationJson->declaration->certification->genre->appellation_CREMANT->detail_lieu_editable = 1;

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
        foreach ($configurationJson->declaration->certification->genre->appellation_GRDCRU->mention as $key_lieu => $lieu) {
            if ($key_lieu == "lieu") {
                @$lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE} = 1;
            }
            if (!preg_match('/^lieu/', $key_lieu) || $key_lieu == "lieu") {
                continue;
            }
            @$lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION} = 1;
        }
        foreach ($configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention as $key_lieu => $lieu) {
            if ($key_lieu == "lieu") {
                @$lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE} = 1;
            }
            if (!preg_match('/^lieu/', $key_lieu) || $key_lieu == "lieu") {
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

        /* Ajout du cépage Pinot noir Raisin */
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_PNRaisin = new stdClass();
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_PNRaisin->libelle = "Pinot Noir";
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_PNRaisin->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION} = 1;
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_PNRaisin->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE} = 1;
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_PNRaisin->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS} = 1;
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_PNRaisin->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS} = 1;

        /* Configuration des produits pour le parcellaire */
        @$configurationJson->declaration->certification->genre->appellation_ALSACEBLANC->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;
        @$configurationJson->declaration->certification->genre->appellation_PINOTNOIR->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;
        @$configurationJson->declaration->certification->genre->appellation_PINOTNOIRROUGE->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;
        @$configurationJson->declaration->certification->genre->appellation_ALSACE->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;
        @$configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;
        @$configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;

        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_BLRS->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_RB->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_PN->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;
        @$configurationJson->declaration->certification->genre->appellation_CREMANT->mention->lieu->couleur->cepage_BN->no_acces->{_ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE} = 1;


        $this->getConfigurationCommunes($configurationJson);

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

    protected function getAlsaceCepages($genreNode) {
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

    protected function getConfigurationCremant($appellations) {
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

    protected function getCepages($appellation, $noeudCouleur = 'couleur') {
        $cepages = new stdClass();
        foreach ($appellation as $m => $mention) {
            if (preg_match('/^mention/', $m)) {
                foreach ($mention as $l => $lieu) {
                    if (preg_match('/^lieu/', $l)) {
                        foreach ($lieu as $co => $couleur) {
                            if (preg_match('/^' . $noeudCouleur . '/', $co)) {
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

    public function getConfigurationCommunes($configurationJson) {
        $communes = array("Albé" => "67",
            "Ammerschwihr" => "68",
            "Andlau" => "67",
            "Avolsheim" => "67",
            "Balbronn" => "67",
            "Barr" => "67",
            "Beblenheim" => "68",
            "Bennwihr" => "68",
            "Bergbieten" => "67",
            "Bergheim" => "68",
            "Bergholtz" => "68",
            "Bergholtz-Zell" => "68",
            "Bernardswiller" => "67",
            "Bernardvillé" => "67",
            "Berrwiller" => "68",
            "Bischoffsheim" => "67",
            "Blienschwiller" => "67",
            "Boersch" => "67",
            "Bourgheim" => "67",
            "Buhl" => "68",
            "Cernay" => "68",
            "Châtenois" => "67",
            "Cleebourg" => "67",
            "Colmar" => "68",
            "Dahlenheim" => "67",
            "Dambach-la-Ville" => "67",
            "Dangolsheim" => "67",
            "Dieffenthal" => "67",
            "Dorlisheim" => "67",
            "Eguisheim" => "68",
            "Eichhoffen" => "67",
            "Epfig" => "67",
            "Ergersheim" => "67",
            "Flexbourg" => "67",
            "Furdenheim" => "67",
            "Gertwiller" => "67",
            "Gimbrett" => "67",
            "Goxwiller" => "67",
            "Gueberschwihr" => "68",
            "Guebwiller" => "68",
            "Hartmannswiller" => "68",
            "Hattstatt" => "68",
            "Heiligenstein" => "67",
            "Herrlisheim" => "68",
            "Houssen" => "68",
            "Hunawihr" => "68",
            "Husseren-les-Châteaux" => "68",
            "Ingersheim" => "68",
            "Irmstett" => "67",
            "Itterswiller" => "67",
            "Jungholtz" => "68",
            "Katzenthal" => "68",
            "Kaysersberg" => "68",
            "Kienheim" => "67",
            "Kientzheim" => "68",
            "Kintzheim" => "67",
            "Kirchheim" => "67",
            "Kuttolsheim" => "67",
            "Marlenheim" => "67",
            "Mittelbergheim" => "67",
            "Mittelwihr" => "68",
            "Molsheim" => "67",
            "Mutzig" => "67",
            "Niedermorschwihr" => "68",
            "Nordheim" => "67",
            "Nothalten" => "67",
            "Oberhoffen-les-Wissembourg" => "67",
            "Obermorschwihr" => "68",
            "Obernai" => "67",
            "Odratzheim" => "67",
            "Orschwihr" => "68",
            "Orschwiller" => "67",
            "Osenbach" => "68",
            "Osthoffen" => "67",
            "Ottrott" => "67",
            "Pfaffenheim" => "68",
            "Reichsfeld" => "67",
            "Ribeauvillé" => "68",
            "Riedseltz" => "67",
            "Riquewihr" => "68",
            "Rodern" => "68",
            "Rorschwihr" => "68",
            "Rosenwiller" => "67",
            "Rosheim" => "67",
            "Rott" => "67",
            "Rouffach" => "68",
            "Saint-Hippolyte" => "68",
            "Saint-Nabor" => "67",
            "Saint-Pierre" => "67",
            "Scharrachbergheim" => "67",
            "Scherwiller" => "67",
            "Sigolsheim" => "68",
            "Soultz" => "68",
            "Soultz-les-Bains" => "67",
            "Soultzmatt" => "68",
            "Steinbach" => "68",
            "Stotzheim" => "67",
            "Thann" => "68",
            "Traenheim" => "67",
            "Turckheim" => "68",
            "Uffholtz" => "68",
            "Vieux-Thann" => "68",
            "Villé" => "67",
            "Voegtlinshoffen" => "68",
            "Walbach" => "68",
            "Wangen" => "67",
            "Wattwiller" => "68",
            "Westhalten" => "68",
            "Westhoffen" => "67",
            "Wettolsheim" => "68",
            "Wihr-au-Val" => "68",
            "Wintzenheim" => "68",
            "Wissembourg" => "67",
            "Wolxheim" => "67",
            "Wuenheim" => "68",
            "Zellenberg" => "68",
            "Zellwiller" => "67",
            "Zimmerbach" => "68");
        $configurationJson->communes = null;
        foreach ($communes as $communeName => $dpt) {
            $configurationJson->communes->$communeName = $dpt;
        }
    }

}
