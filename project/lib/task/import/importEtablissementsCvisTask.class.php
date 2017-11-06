<?php

class importEtablissementsCvisTask extends sfBaseTask
{

    CONST ETABLISSEMENT_ID = 0;
    CONST ETABLISSEMENT_TYPE = 1;
    CONST ETABLISSEMENT_CVI = 2;


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
        $this->name = 'etablissements-cvis';
        $this->briefDescription = "Création des établissements par cvis";
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
            if(preg_match("/^id/", $line)) {
                continue;
            }

            $data = str_getcsv($line, ';');
            if($data[self::ETABLISSEMENT_TYPE] == "bailleur"){
              echo "Pas d'import Bailleur !!\n";
              continue;
            }
            if(("24150" == $data[self::ETABLISSEMENT_ID]) || ("42921" == $data[self::ETABLISSEMENT_ID]) || ("44659" == $data[self::ETABLISSEMENT_ID]) || ("16068" == $data[self::ETABLISSEMENT_ID]) || ("16068" == $data[self::ETABLISSEMENT_ID])){
              echo $data[self::ETABLISSEMENT_ID]."A Virer !!\n";
              continue ;
            }

             try{
                $this->importEtablissementLine($data);
             } catch (Exception $e) {
                 echo sprintf("ERROR;%s;#LINE;%s\n", $e->getMessage(), $line);
                 continue;
             }
        }
    }

    protected function importEtablissementLine($dataLine){

      $id = "SOCIETE-".sprintf("%06d",$dataLine[self::ETABLISSEMENT_ID]);
      $societe = SocieteClient::getInstance()->find($id);
      if(!$societe){
        echo "La société $id n'existe pas\n";
        return;
      }
      $etbs = $societe->getEtablissementsObj();
      $etbFounded = false;

      if(!count($etbs)){
        $this->createEtablissement($dataLine,$societe);
        return ;
      }

      foreach ($etbs as $key => $etb) {
        if($etb->etablissement->cvi == trim($dataLine[self::ETABLISSEMENT_CVI])){
          $etbFounded = $etb->etablissement;
          echo "L'etablissement $etbFounded->_id de cvi $etbFounded->cvi existe, pas d'import \n";
          return ;
        }
      }
      if(count($etbs) == 1){
        foreach ($etbs as $key => $etb) {
          if(!$etb->etablissement->cvi){
            $etb = $etb->etablissement;
            $cvi = trim($dataLine[self::ETABLISSEMENT_CVI]);
            $etb->cvi = $cvi;
            echo "L'etablissement $etb->_id vas avoir le CVI $cvi \n";
            $etb->save();
            return ;
          }
        }
      }
      if(count($etbs) > 1){
        foreach ($etbs as $key => $etb) {
          if(!$etb->etablissement->cvi){
            $etb = $etb->etablissement;
            $cvi = trim($dataLine[self::ETABLISSEMENT_CVI]);
            $etb->cvi = $cvi;
            echo "/!\ On assigne le cvi au premier etb ? $etb->_id  \n";
            $etb->save();
            return ;
            }
          }
        }

      if(!$etbFounded){
        return $this->createEtablissement($dataLine,$societe);
        }
    }

    public function createEtablissement($dataLine,$societe){
        $id = $dataLine[self::ETABLISSEMENT_ID];
        $etb = $societe->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
        $etb->nom = $societe->raison_sociale;
        $etb->cvi = trim($dataLine[self::ETABLISSEMENT_CVI]);
        echo "L'etablissement $etb->_id de cvi $etb->cvi a été créé \n";
        return $etb->save();
    }
}
