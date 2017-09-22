<?php

class exportDouaneStatsTask extends sfBaseTask
{

    protected function configure()
    {

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'export';
        $this->name = 'douane-stats';
        $this->briefDescription = 'Export global des données de DR, SV11, SV12';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $target = sfConfig::get('sf_data_dir').'/douane-stats';
        $filename = date('Y-m-d').'.csv';
        
        if (!is_dir($target)) {
        	mkdir($target);
        }

        if (!$handle = fopen($target.'/'.$filename, 'a')) {
			$this->logSection('douane-stats', "Impossible d'ouvrir le fichier ($filename)", null, 'ERROR');
        	return;
        }
        if (fwrite($handle, utf8_encode(DouaneCsvFile::CSV_ENTETES)) === FALSE) {
        	$this->logSection('douane-stats', "Impossible d'écrire dans le fichier ($filename)", null, 'ERROR');
        }
        
        $pieces = PieceAllView::getInstance()->getAll();
        $nb = count($pieces);
        $counter = 0;
        foreach ($pieces as $piece) {
        	$counter++;
        	if (preg_match('/^DR/', $piece->key[PieceAllView::KEYS_LIBELLE])) {
        		$type = 'DRDouaneCsvFile';
        	} elseif (preg_match('/^SV11/', $piece->key[PieceAllView::KEYS_LIBELLE])) {
        		$type = 'SV11DouaneCsvFile';
        	} elseif (preg_match('/^SV12/', $piece->key[PieceAllView::KEYS_LIBELLE])) {
        		$type = 'SV12DouaneCsvFile';
        	} else {
        		continue;
        	}
        	$fichier = FichierClient::getInstance()->find($piece->id);
        	if ($file = $fichier->getFichier('csv')) {
        		$csv = new $type($file);
        		if (fwrite($handle, utf8_encode($csv->convert())) === FALSE) {
        			$this->logSection('douane-stats', "Impossible d'écrire dans le fichier ($filename)", null, 'ERROR');
        		} else {
        			$this->logSection('douane-stats', "Push csv ok (".round(($counter/$nb)*100,1)."%)");
        		}
        	}
        }
        fclose($handle);
    }

    
}