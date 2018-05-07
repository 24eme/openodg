<?php

class ParcellaireChangeKeyTask extends sfBaseTask
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
        ));

        $this->namespace = 'parcellaire';
        $this->name = 'change-old-keys';
        $this->briefDescription = "Permet de changer les clé de CDR à CDP";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $routing = clone ProjectConfiguration::getAppRouting();
        $context = sfContext::createInstance($this->configuration);
        $context->set('routing', $routing);
        $oneChange = false;
        $parcellaire = ParcellaireClient::getInstance()->find($arguments['doc_id']);
        $declarations = array();
        foreach($parcellaire->declaration as $key => $value) {
            if(preg_match('/\/CDR\//',$key)){

                $declarations[str_replace('CDR','CDP',$key)] = $value;
                $oneChange = true;
                echo "la clé ".$key." a été bougé\n";
            }else{
                $declarations[$key] = $value;
            }
        }

        if($oneChange){
            $parcellaire->remove('declaration');
            $d = $parcellaire->add("declaration");
            foreach ($declarations as $key => $value) {
                $d->add($key,$value);
            }
            $parcellaire->save();
            echo sprintf("%s SUCCESS : parcellaire corrigé\n",$parcellaire->_id);
            }else{
                echo sprintf("%s PAS de changements\n",$parcellaire->_id);
            }

        }

}
