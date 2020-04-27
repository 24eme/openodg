<?php

class RegistreVCIClotureTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "ID document"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'registrevci';
        $this->name = 'cloture';
        $this->briefDescription = 'Met tous les stocks à zero lorsque tout le stock a été utilisé';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $registrevci = RegistreVCIClient::getInstance()->find($arguments['doc_id']);
        $rev = $registrevci->_rev;
        $registrevci->clotureStock();
        $registrevci->save();

        if($rev != $registrevci->_rev) {
                echo $registrevci->_id." saved\n";
        }

    }

}
