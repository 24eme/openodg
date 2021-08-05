<?php

class DRevCompareToHabilitationTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document DRev ID"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'compare-to-habilitation';
        $this->briefDescription = 'compare drev avec habilitation';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $drev = DRevClient::getInstance()->find($arguments['doc_id']);

        $habilitation = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($drev->identifiant, $drev->date);

        if (!$habilitation) {
            echo "INCOHERENCE: ".$drev->_id." pas d'habilitation au ".$drev->date;
            echo $drev->isNonVinificateur() ? ' (non vinificateur)' : '';
            echo "\n";
            return;
        }
        
        foreach($habilitation->getProduitsHabilites() as $p) {
            if (!$drev->exist($p->getHash())) {
                continue;
            }
            if ($drev->get($p->getHash())->getTotalTotalSuperficie()) {
                //doit être producteur
                if (!$p->activites->exist(HabilitationClient::ACTIVITE_PRODUCTEUR) ||
                    !$p->activites->get(HabilitationClient::ACTIVITE_PRODUCTEUR)->statut == HabilitationClient::STATUT_HABILITE) {
                        echo "INCOHERENCE: ".$habilitation->_id." ".$p->getLibelle()." : ";
                        echo "devrait être producteur au ".$drev->date."\n";
                    }
            }
            if ($drev->get($p->getHash())->getTotalVolumeRevendique()) {
                if (!$p->activites->exist(HabilitationClient::ACTIVITE_VINIFICATEUR) ||
                    !$p->activites->get(HabilitationClient::ACTIVITE_VINIFICATEUR)->statut == HabilitationClient::STATUT_HABILITE) {
                        echo "INCOHERENCE: ".$habilitation->_id." ".$p->getLibelle()." : ";
                        echo "devrait être vinificateur au ".$drev->date."\n";
                    }
            }
        }

    }
}
