<?php

class ImportDocumentsDouaniersTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('annee', sfCommandArgument::REQUIRED, "Campagne au format AAAA"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),

        	new sfCommandOption('type', null, sfCommandOption::PARAMETER_OPTIONAL, "Type de document", null),
            new sfCommandOption('forceimport', null, sfCommandOption::PARAMETER_OPTIONAL, "Force import document (exept if manualy edited)", false),
            new sfCommandOption('scrape', null, sfCommandOption::PARAMETER_OPTIONAL, "Scrape the document", false),
        ));

        $this->namespace = 'import';
        $this->name = 'documents-douaniers';
        $this->briefDescription = "Import des documents douaniers";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $contextInstance = sfContext::createInstance($this->configuration);

        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $annee = $arguments['annee'];
        $type = ($options['type'])? strtoupper($options['type']) : null;

        if (!preg_match('/^[0-9]{4}$/', $annee)) {
        	echo sprintf("ERROR;Année non valide %s\n", $annee);
        	return;
        }

        if ($type && !in_array($type, $this->getDocumentDouanierTypes())) {
        	echo sprintf("ERROR;Type non valide %s\n", $type);
        	return;
        }

        $items = EtablissementAllView::getInstance()->findByInterproStatutAndFamilleVIEW('INTERPRO-declaration', 'ACTIF', null);
		    $i=0;
		    $nb = count($items);
        foreach ($items as $item) {


        	$i++;
        	echo "PROCESSUS;".$i.'/'.$nb.' => '.floor($i / $nb * 100)."\n";

        	if ($etablissement = EtablissementClient::getInstance()->find($item->id)) {
        		$ddType = $this->getDocumentDouanierType($etablissement);

        		if ($type && $ddType != $type) {
        			continue;
        		}

        		if (!$etablissement->cvi || !preg_match('/^[0-9]{10}$/', $etablissement->cvi)) {
        			echo sprintf("ERROR;CVI non valide %s pour %s\n", $etablissement->cvi, $etablissement->_id);
        			continue;
        		}

        		if (!$ddType) {
        			echo sprintf("ERROR;Famille non identifiée %s pour %s\n", $etablissement->famille, $etablissement->_id);
        			continue;
        		}

        		$c = FichierClient::getInstance()->getClientFromType($ddType);

                if ($f = $c->findByArgs($etablissement->identifiant, $annee) && (!($options['forceimport']) || ($f && $f->exist('donnees')))) {
                    if (!$options['forceimport']) {
                        echo sprintf("WARNING;Document douanier déjà existant %s\n", $f->_id);
                    }else{
                        echo sprintf("WARNING;Document douanier en saisie interne %s\n", $f->_id);
                    }
        			continue;
        		}

        		try {
        			$result = FichierClient::getInstance()->scrapeAndSaveFiles($etablissement, $ddType, $annee, ($options['scrape']), $contextInstance);
        		} catch (Exception $e) {
        			echo sprintf("ERROR;%s\n", $e->getMessage());
        			continue;
        		}

        		if (!$result) {
        			echo sprintf("WARNING;Aucun document douanier pour %s (%s)\n", $etablissement->_id, $etablissement->cvi);
        		} else {
        			echo sprintf("SUCCESS;Document douanier importé;%s\n", $result->_id);
        		}

        	} else {
        		echo sprintf("ERROR;Etablissement non trouvé %s\n", $item->id);
        	}
        }
    }

    protected function getDocumentDouanierType($etablissement)
    {
        if($etablissement->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR || $etablissement->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR) {
            return DRCsvFile::CSV_TYPE_DR;
        }

        if($etablissement->famille == EtablissementFamilles::FAMILLE_COOPERATIVE) {
            return SV11CsvFile::CSV_TYPE_SV11;
        }

        if($etablissement->famille == EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR || $etablissement->famille == EtablissementFamilles::FAMILLE_NEGOCIANT) {
            return SV12CsvFile::CSV_TYPE_SV12;
        }
        return null;
    }

    protected function getDocumentDouanierTypes()
    {
    	return array(
    			DRCsvFile::CSV_TYPE_DR,
    			SV11CsvFile::CSV_TYPE_SV11,
    			SV12CsvFile::CSV_TYPE_SV12
    	);
    }
}
