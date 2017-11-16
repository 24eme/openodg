<?php

class fixSocieteEtablissementStatutTask extends sfBaseTask
{

    protected function configure()
    {

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'societes-etablissements-statuts';
        $this->briefDescription = "Fixe du numéro d'archivage des comptes";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(SocieteAllView::getInstance()->findByInterpro('INTERPRO-declaration') as $socdata) {
          if(!$socdata->key[1]){
            $societe = SocieteClient::getInstance()->find($socdata->id);
            $societe->setStatut(SocieteClient::STATUT_ACTIF);
            try{
              $societe->save();
            }catch(sfException $e){
              echo sprintf("PROBLEME;%s;%s\n", "Societe    ", $socdata->id);
            }
            echo sprintf("UPDATE;%s;%s;%s\n", "Societe    ", $socdata->id, $societe->statut);
          }
        }
        foreach(EtablissementAllView::getInstance()->findByInterpro('INTERPRO-declaration')->rows as $etbdata) {
          if(!$etbdata->key[1]){
            $etablissement = SocieteClient::getInstance()->find($etbdata->id);
            $etablissement->setStatut(SocieteClient::STATUT_ACTIF);
            $etablissement->save();
            echo sprintf("UPDATE;%s;%s;%s\n", "ETABLISSEMENT    ", $etbdata->id, $etbdata->statut);
          }
        }
    }
}
