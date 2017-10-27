<?php
class importRelationsEtablissementsTask extends sfBaseTask
{
    CONST ETABLISSEMENT_CVI_SRC = 0;
    CONST ETABLISSEMENT_PPM_DEST = 1;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file_path', sfCommandArgument::REQUIRED, "Fichier csv pour l'import")
        ));
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
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
        $file_path = $arguments['file_path'];
        if(!$file_path){
          throw new  sfException("Le paramètre du fichier csv doit être renseigné");
        }
        error_reporting(E_ERROR | E_PARSE);
        foreach(file($file_path) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^(id;|;;;;;)/", $line)) {
                continue;
            }
            $data = str_getcsv($line, ';');
            try{
                $this->importRelationLine($data);
            } catch (Exception $e) {
                echo sprintf("ERROR;%s;#LINE;%s\n", $e->getMessage(), $line);
                $doc = null;
                continue;
            }
        }
    }

    protected function importRelationLine($dataLine){

      $cvi = $dataLine[self::ETABLISSEMENT_CVI_SRC];
      $etablissement_metayer = EtablissementClient::getInstance()->findByCvi($cvi);
      if(!$etablissement_metayer){
        echo "/!\ l'etablissement metayer $cvi n'a pas été trouvé dans la base.\n";
        return ;

      }
      $ppm = $dataLine[self::ETABLISSEMENT_PPM_DEST];
      $etablissement_bailleur = EtablissementClient::getInstance()->findByPPM($ppm);
      if(!$etablissement_bailleur){
        echo "/!\ l'etablissement bailleur $ppm n'a pas été trouvé dans la base.\n";
          return ;

      }
      echo "IMPORT l'etablissement $ppm est bailleur du metayer $cvi ";
      $this->addLiaison($etablissement_bailleur,$etablissement_metayer);
    }

    function addLiaison($etbBailleur,$etbMetayer){

        $liaisons_operateurs_bailleur = $etbBailleur->getOrAdd('liaisons_operateurs');

        $idLiaisonBailleur = EtablissementClient::TYPE_LIAISON_BAILLEUR . '_' . $etbMetayer->_id;
        if($liaisons_operateurs_bailleur->exist($idLiaisonBailleur) && $liaisons_operateurs_bailleur->get($idLiaisonBailleur)){
          echo "- La liaison existe déjà chez le bailleur ";
        }else{
          $etbBailleur->addLiaison(EtablissementClient::TYPE_LIAISON_BAILLEUR,$etbMetayer);
          $etbBailleur->save();
          $compteBailleur = CompteClient::getInstance()->find($etbBailleur->getCompte());
          $compteBailleur->addTag('manuel',EtablissementClient::TYPE_LIAISON_BAILLEUR);
          $compteBailleur->save();
          echo "- Liaison créée chez le bailleur ";
        }

        $liaisons_operateurs_metayer = $etbMetayer->getOrAdd('liaisons_operateurs');

        $idLiaisonMetayer = EtablissementClient::TYPE_LIAISON_METAYER . '_' . $etbBailleur->_id;
        if($liaisons_operateurs_metayer->exist($idLiaisonMetayer) && $liaisons_operateurs_metayer->get($idLiaisonMetayer)){
          echo "- La liaison existe déjà chez le metayer ";
        }else{
          $etbMetayer->addLiaison(EtablissementClient::TYPE_LIAISON_METAYER,$etbBailleur);
          $etbMetayer->save();
          $compteMetayer = CompteClient::getInstance()->find($etbMetayer->getCompte());
          $compteMetayer->addTag('manuel',EtablissementClient::TYPE_LIAISON_METAYER);
          $compteMetayer->save();
          echo "- Liaison créée chez le metayer ";
        }
        echo "\n";
      }
}
