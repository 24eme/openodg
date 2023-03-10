<?php

class FixParcellaireTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('parcellaireid', sfCommandArgument::REQUIRED, "Donnees au format CSV")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'parcellaire';
        $this->briefDescription = "Corrige le parcellaire passé en parametre";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $p = ParcellaireAffectationClient::getInstance()->find($arguments['parcellaireid']);
        $pPrev = $p->getParcellaireLastCampagne();

        if(!$pPrev) {
            return;
        }

        if($pPrev->exist('declaration')) {
            foreach ($pPrev->declaration->getProduitsCepageDetails() as $parcelle) {
                if(!$parcelle->active) {
                    continue;
                }
                $parcellesActives[$parcelle->getHash()] = $parcelle->getHash();
                if($parcelle->getLieu()) {
                    $parcellesLieux[$parcelle->getHash()] = $parcelle->getLieu();
                    $parcellesLieux[$parcelle->getSectionNumero()] = $parcelle->getLieu();
                }
            }
        }
        if($p->exist('declaration/certification/genre/appellation_LIEUDIT')) {
            foreach($p->get('declaration/certification/genre/appellation_LIEUDIT')->getProduitsCepageDetails() as $parcelle) {
                if(!isset($parcellesLieux[$parcelle->getSectionNumero()])) {
                    continue;
                }
                if($parcelle->lieu == $parcellesLieux[$parcelle->getSectionNumero()]) {
                    continue;
                }
                $oldLieu = $parcelle->lieu;
                $parcelle->lieu = $parcellesLieux[$parcelle->getSectionNumero()];
                if($parcelle->getActive() && KeyInflector::slugify($oldLieu) != KeyInflector::slugify($parcelle->lieu)) {
                    echo $p->_id.';'.boolval($p->validation).';'.boolval($p->validation_odg).';'.$parcelle->getSection().";".$parcelle->getNumeroParcelle().";".$oldLieu.";".$parcelle->lieu.";".$parcelle->getHash()."\n";
                }
            }
        }

        //$p->save();
    }
}
