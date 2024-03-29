<?php

class DeclarationRegenerateMouvementsTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('onlydeletemouvements', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', false),
            new sfCommandOption('flagfacture', null, sfCommandOption::PARAMETER_REQUIRED, 'set the mouvement to facture = 1', false),
            new sfCommandOption('createnewmodif', null, sfCommandOption::PARAMETER_REQUIRED, 'créer une modificatrice au lieu de mettre les mouvements dans le document passé en argument', false),
        ));

        $this->namespace = 'declaration';
        $this->name = 'regenerate-mouvements';
        $this->briefDescription = "Regénère les mouvements de facturation d'un document";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();


        $drev = DeclarationClient::getInstance()->find($arguments['doc_id']);
        if ($options['createnewmodif']) {
            $drev = $drev->generateModificative();
            $drev->validate();
            $drev->validateOdg();
            $drev->save();
        }

        $is_facture = 0;
        foreach($drev->mouvements as $id => $mvts ) {
            foreach ($mvts as $key => $mvt) {
                if ($mvt->facture) {
                    $is_facture = 1;
                    echo sprintf("ERROR;Des mouvements déjà facturés;%s\n", $drev->_id);
                    exit(1);
                }
            }
        }

        $drev->remove('mouvements');
        $drev->add('mouvements');
        if (!$options['onlydeletemouvements']) {
            $drev->generateMouvementsFactures();
        }
        if ($options['flagfacture']) {
            foreach($drev->mouvements as $id => $mvts ) {
                foreach ($mvts as $key => $mvt) {
                    $mvt->facture = 1;
                }
            }
        }
        $drev->save();
        echo sprintf("SUCCESS;Les mouvements ont été regénérés;%s\n", $drev->_id);
    }
}
