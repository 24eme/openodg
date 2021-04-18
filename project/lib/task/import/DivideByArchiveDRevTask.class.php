<?php

class divideByArchiveDRevTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('drevid', sfCommandArgument::REQUIRED, "DREV id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'divide';
        $this->briefDescription = 'Division des DRev';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $drev = DRevClient::getInstance()->find($arguments['drevid']);
        if (!$drev) {
            return;
        }
        $lots_by_dossier = array();
        foreach ($drev->lots as $lot) {
            if (!isset($lots_by_dossier[$lot->numero_dossier])) {
                $lots_by_dossier[$lot->numero_dossier] = array();
            }
            $lots_by_dossier[$lot->numero_dossier][] = $lot;
        }
        if (count(array_keys($lots_by_dossier)) <= 1) {
            return;
        }
        $lots = array();
        $affectations = array();
        $numeros_dossier = array_keys($lots_by_dossier);
        sort($numeros_dossier);
        $current_drev = $drev;
        foreach($numeros_dossier as $num) {
            $current_drev->numero_archive = $num;
            $current_drev->validation = $drev->validation;
            $current_drev->validation_odg = $drev->validation_odg;
            echo $current_drev->_id." saving\n";
            $current_drev->save();
            $drev_modif = $current_drev->generateModificative();
            $hash_to_remove = array();
            foreach($current_drev->lots as $lot) {
                if ($lot->numero_dossier > $num) {
                    $hash_to_remove[] = $lot->getHash();
                }
            }
            rsort($hash_to_remove);
            foreach($hash_to_remove as $hash) {
                $current_drev->remove($hash);
            }
            foreach($current_drev->lots as $lot) {
                if ($lot->numero_dossier != $num) {
                    continue;
                }
                $lot->id_document = $current_drev->_id;
                $affectations[$lot->id_document_affectation.$lot->unique_id] = array(
                        'unique_id' => $lot->unique_id,
                        'id_document_affectation' => $lot->id_document_affectation,
                        'new_provenance' => $current_drev->_id,
                );
            }
            $current_drev->generateMouvementsLots();
            $current_drev->save();
            $current_drev = $drev_modif;
        }
        $docs = array();
        $drevs = array();
        foreach($affectations as $k => $a) {
            if (!isset($docs[$a['id_document_affectation']])) {
                $docs[$a['id_document_affectation']] = acCouchdbManager::getClient()->find($a['id_document_affectation']);
            }
            $doc = $docs[$a['id_document_affectation']];
            $l = $doc->getLot($a['unique_id']);
            $l->id_document_provenance = $a['new_provenance'];
            $drevs[$a['new_provenance']] = $a['new_provenance'];
        }
        foreach($docs as $k => $doc) {
            echo $doc->_id." saving\n";
            $doc->save(false);
        }
    }
}