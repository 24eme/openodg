<?php
class importRelationsEtablissementsTask extends sfBaseTask
{
    CONST ETABLISSEMENT_CVI_SRC = 0;
    CONST ETABLISSEMENT_PPM_DEST = 1;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('etablissement_source_id', sfCommandArgument::REQUIRED, "Etablissement source (id ou cvi)"),
            new sfCommandArgument('etablissement_destination_id', sfCommandArgument::REQUIRED, "Etablissement destination (id ou cvi)"),
            new sfCommandArgument('relations_type', sfCommandArgument::REQUIRED, "Type de relation")
        ));
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('remove', null, sfCommandOption::PARAMETER_OPTIONAL, 'Delete relation', false),
            new sfCommandOption('both_side', null, sfCommandOption::PARAMETER_OPTIONAL, 'Delete or creation relation on both side', true),
        ));
        $this->namespace = 'import';
        $this->name = 'relations-etablissements';
        $this->briefDescription = "Création des Relations entre les établissements";
        $this->detailedDescription = <<<EOF
EOF;
    }
    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $types_liaisons = EtablissementClient::getInstance()->getTypesLiaisons();
        if (!$types_liaisons[$arguments['relations_type']]) {
            print_r($types_liaisons);
            throw new sfException('relation '.$arguments['relations_type'].' non connue');
        }
        $etablissement_source = EtablissementClient::getInstance()->find($arguments['etablissement_source_id']);
        if (!$etablissement_source) {
            $etablissement_source = EtablissementClient::getInstance()->findByCvi($arguments['etablissement_source_id']);
        }
        if (!$etablissement_source) {
            throw new sfException('Etablissement '.$arguments['etablissement_source_id'].' non trouvé');
        }

        $etablissement_dest = EtablissementClient::getInstance()->find($arguments['etablissement_destination_id']);
        if (!$etablissement_dest) {
            $etablissement_dest = EtablissementClient::getInstance()->findByCvi($arguments['etablissement_destination_id']);
        }
        if (!$etablissement_dest) {
            throw new sfException('Etablissement '.$arguments['etablissement_destination_id'].' non trouvé');
        }
        if($options['remove']) {
            $etablissement_source->removeLiaison($arguments['relations_type']."_".$etablissement_dest, (bool) $options['both_side']);
        } else {
            $etablissement_source->addLiaison($arguments['relations_type'], $etablissement_dest, (bool) $options['both_side']);
        }

        $etablissement_source->save();

    }
}
