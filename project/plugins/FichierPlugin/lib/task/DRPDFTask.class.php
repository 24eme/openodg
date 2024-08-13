<?php

class DRPDFTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('dr_id', sfCommandArgument::REQUIRED, "Id de la DRev"),
            new sfCommandArgument('file_path', sfCommandArgument::REQUIRED, "Path where the DR should be saved"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'dr';
        $this->name = 'pdf';
        $this->briefDescription = "Genere le pdf d'une DR";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $dr = FichierClient::getInstance()->find($arguments['dr_id']);
        $file_path = $arguments['file_path'];
        umask(0);
        mkdir(dirname($file_path), 0755, true);

        if(!$dr) {
            die("DR ".$arguments['dr_id']." non trouvée");
        }
        foreach ($dr->_attachments as $key => $attachement) {
            if ($attachement->content_type == 'application/pdf') {
                if (!file_put_contents($file_path, file_get_contents($dr->getAttachmentUri($key)))) {
                   die("could not save ".$file_path);
                }
                return;
            }
        }
    }

}
