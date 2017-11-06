<?php

class importCommentairesTask extends sfBaseTask
{

  const CSV_ID_IDENTITE = 0;
  const CSV_COMMENTAIRE = 1;

    protected $types_ignore = array();

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'Commentaires';
        $this->briefDescription = 'Import des commentaires² (via le csv issu de scrapping)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $datas = array();
        foreach(file($arguments['file']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');

            $id = sprintf('%06d', $data[self::CSV_ID_IDENTITE]);
            echo "trying $id \n";
            $soc = SocieteClient::getInstance()->find($id);
            if (!$soc) {
              echo "ERROR: pas de société trouvée pour : ".$id."\n";
              continue;
            }
            $etablissement = $soc->getEtablissementPrincipal();
            if (!$etablissement) {
              echo "ERROR: pas d'établissement trouvé pour la société ".$id."\n";
              continue;
            }

            foreach (explode('<br>', $data[self::CSV_COMMENTAIRE]) as $id => $commentaire) {
              if (!$commentaire) {
                continue;
              }
              $etablissement->addCommentaire($commentaire);
            }

            $etablissement->save();
            echo $etablissement->_id."\n";
        }
    }
}
