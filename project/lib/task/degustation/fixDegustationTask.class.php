<?php

class FixDegustationTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('tournee_id', sfCommandArgument::REQUIRED, "Tournee ID")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'Degustation';
        $this->briefDescription = "Corrige le parcellaire passé en parametre";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tournee = TourneeClient::getInstance()->find($arguments['tournee_id']);

        if(!$tournee) {

            throw new sfException("Tournee introuvable");
        }

        if(!$tournee->exist('operateurs')) {
            echo "Tournee déja converti\n";
            return;
        }

        foreach($tournee->_get('operateurs') as $operateur) {
            $degustation = DegustationClient::getInstance()->findOrCreate($operateur->getKey(), $tournee->date, $tournee->appellation);
            $datas = $operateur->toArray(true, false);

            foreach($datas as $key => $data) {
                if($key == "degustation") {
                    continue;
                }
                $degustation->add($key, $data);
            }

            $degustation->date_prelevement = $degustation->date;
            $degustation->remove('lng');
            $degustation->remove('date');
            foreach($degustation->prelevements as $prelevement) {
                if(strlen($prelevement->anonymat_prelevement) <= 3) {
                    continue;
                }
                $prelevement->anonymat_prelevement_complet =$prelevement->anonymat_prelevement;
                $prelevement->anonymat_prelevement = (int) substr($prelevement->anonymat_prelevement, 2, 3);
            }
            $degustation->save();
            $tournee->degustations->add($degustation->identifiant, $degustation->_id);
        }

        $tournee->remove('operateurs');
        $tournee->save();
    }
}