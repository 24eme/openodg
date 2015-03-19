<?php

class ImportParcellaireTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('annee', sfCommandArgument::REQUIRED, "Annee de declaration"),
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Donnees au format CSV")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'Parcellaire';
        $this->briefDescription = "Importe le parcellaire depuis le CSV d'une annee";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $convert = array('COMMUNALE' => "AOC Alsace Communale", 'LIEUDIT' => 'aoc alsace lieudit', 'GRDCRU' => 'aoc alsace grand cru ', 'CREMANT' => 'aoc cremant dalsace');
        $i = 0;
        $sep = '';
        foreach(file($arguments['csv']) as $line) {
            if (!$sep) {
                $sep = (count(explode(',', $line)) > count(explode(';', $line))) ? ',' : ';';
            }
            $csv = str_getcsv($line, $sep);
            $i++;
            if ($csv[0] == 'ORIGINE') {
                continue;
            }
            try {
                $p = ParcellaireClient::getInstance()->findOrCreate($csv[8], $arguments['annee']);
            }catch(sfException $e) {
                print "ERROR: ligne $i: ".$e->getMessage()."\n";
                continue;
            }
            if (!$p->isNew()) {
                if (count($csv) < 14) {
                    print "WARNING: ligne $i: ligne contient moins de 14 champs :(\n";
                }
                $p->add('declarant')->add('cvi', $csv[8]);
                $p->add('declarant')->add('nom', $csv[9]);
                $p->add('declarant')->add('adresse', $csv[10]);
                $p->add('declarant')->add('code_postal', $csv[11]);
                $p->add('declarant')->add('commune', $csv[12]);
                $p->add('declarant')->add('telephone', $csv[13]);
                $p->add('declarant')->add('email', $csv[14]);
            }

            $hash = $p->getConfiguration()->identifyProduct($convert[$csv[0]], $csv[2], $csv[5], _ConfigurationDeclaration::TYPE_DECLARATION_PARCELLAIRE);
            if (isset($hash['error'])) {
                print("ERROR: ligne $i: Pas de produit pour $csv[0] / $csv[2] / $csv[5] (".$hash['error'].")\n");
                continue;
            }
            $produit = $p->getOrAdd($hash['hash']);
            $produit->getLibelleComplet();
            if($produit->getConfig()->hasLieuEditable()) {
                print("SUCCESS: ligne $i: $csv[0] / $csv[5] (".$hash['hash'].")\n");
            } else {
                print("SUCCESS: ligne $i: $csv[0] / $csv[2] / $csv[5] (".$hash['hash'].")\n");
            }
            $parcelle = $produit->add('detail')->add(KeyInflector::slugify($csv[1].'_'.$csv[3].'_'.$csv[4]));
            $parcelle->commune = strtoupper($csv[1]);
            $parcelle->section = $csv[3];
            $parcelle->numero_parcelle = $csv[4];
            $parcelle->superficie = str_replace(',', '.', $csv[6]) * 1;
            if($parcelle->getCepage()->getConfig()->hasLieuEditable()) {
                $parcelle->lieu = trim($csv[2]);
            }
            $isNew = $p->isNew();
            try{
                $p->validation = true;
                $p->validation_odg = true;

                $p->save();
            }catch(sfException $e) {
                print "ERROR: ligne $i: ".$e->getMessage()."\n";
                continue;
            }
            if ($isNew) {
                print($p->_id." created\n");
            }
        }
    }
}