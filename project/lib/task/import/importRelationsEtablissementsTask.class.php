<?php

class importRelationsEtablissementsTask extends sfBaseTask
{

    CONST ETABLISSEMENT_SRC = 0;
    CONST ORDRE = 1;
    CONST ETABLISSEMENT_SRC_ROLE = 2;
    CONST ETABLISSEMENT_SRC_RATIO = 3;
    CONST ETABLISSEMENT_DEST = 4;
    CONST ETABLISSEMENT_DEST_NOM = 5;
    CONST ETABLISSEMENT_DEST_ROLE = 6;
    CONST ETABLISSEMENT_DEST_RATIO = 7;
    CONST ETABLISSEMENT_DEST_CVI = 7;

    const ROLE_BAILLEUR = 'Bailleur';
    const ROLE_FERMIER = 'Fermier';
    const ROLE_METAYER = 'Métayer';


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

      if(($dataLine[self::ETABLISSEMENT_SRC_ROLE] == self::ROLE_FERMIER) || ($dataLine[self::ETABLISSEMENT_SRC_ROLE] == self::ROLE_METAYER) || ($dataLine[self::ETABLISSEMENT_SRC_ROLE] == self::ROLE_BAILLEUR)){
        $this->importRelation2Etbs($dataLine,$dataLine[self::ETABLISSEMENT_SRC_ROLE]);
      }
    }

    protected function importRelation2Etbs($dataLine,$srcRole){
      $idSrc = "SOCIETE-".sprintf("%06d",$dataLine[self::ETABLISSEMENT_SRC]);
      $societeSrc = SocieteClient::getInstance()->find($idSrc);
      $idDest = "SOCIETE-".sprintf("%06d",$dataLine[self::ETABLISSEMENT_DEST]);
      $societeDest = SocieteClient::getInstance()->find($idDest);


      if(!$societeSrc){
        echo "/!\ La société d'identifiant $idSrc n'existe pas dans la base [".implode(',',$dataLine)."]\n";
        return;
      }
      if(!$societeDest){
        echo "/!\ La société d'identifiant $idDest n'existe pas dans la base [".implode(',',$dataLine)."]\n";
        return;
      }
      if($dataLine[self::ETABLISSEMENT_DEST] == "21201"){
        echo "$idSrc : /!\ Bailleur inconnu, pas de liaison créée. \n";
        return;
      }
      if($dataLine[self::ETABLISSEMENT_SRC] == "21201"){
        echo "$idSrc : /!\ Bailleur inconnu, a un $srcRole : $idDest , pas de liaison créée. \n";
        return;
      }

      $etbsSrc = $societeSrc->getEtablissementsObj();

      $etbSrc = null;
      $etbDst = null;

      if(!count($etbsSrc)){
        if($srcRole == self::ROLE_BAILLEUR){
          $etbSrc = $societeSrc->createEtablissement('OPERATEUR');
          $etbSrc->nom = $societeSrc->raison_sociale;
        }else{
          echo "/!\ $idSrc $srcRole n'a aucun ETBS ";
        }
      }elseif(count($etbsSrc) > 1){
        foreach ($etbsSrc as $key => $etb) {
          $etbSrc = $etb->etablissement;
          break;
        }
        echo "$idSrc $srcRole a plusieurs ETBS on integre la liaison dans le premier : $etbSrc->_id !! [".implode(',',$dataLine)."]\n";
      }else{
        foreach ($etbsSrc as $key => $etb) {
          $etbSrc = $etb->etablissement;
          break;
        }
      }

      $etbsDst = $societeDest->getEtablissementsObj();
      if(!count($etbsDst)){
          echo "$societeDest->_id n'a pas d'établissements.";
      }elseif(count($etbsDst) > 1){
        foreach ($etbsDst as $key => $etb) {
          if($etb->etablissement->cvi == $dataLine[self::ETABLISSEMENT_DEST_CVI]){
            $etbDst = $etb;
            echo "L'etablissement destinataire $etbDst->_id a été repéré par son CVI $etb->cvi ";
            break;
          }
        }
        if(!$etbDst){
          echo "La société Destinataire $idDest a plusieurs ETBS et aucun de ses CVIs ne match [".implode(',',$dataLine)."]\n";
        }
      }else{
          foreach ($etbsDst as $key => $etb) {
            $etbDst = $etb->etablissement;
            break;
          }
      }

      if($etbSrc && $etbDst){
        echo $etbSrc->_id." ".$srcRole." de ".$etbDst->_id." ";
        $this->addLiaison($etbSrc,$etbDst,$srcRole,$dataLine);
      }else{
        echo " /!\ Un des etb n'est pas identifié => pas d'import ".implode(',',$dataLine)."\n";
      }


    }

    function addLiaison($etbSrc,$etbDst,$srcRole,$dataLine){
        $liaisons_operateurs = $etbSrc->getOrAdd('liaisons_operateurs');
        $idLiaison = KeyInflector::slugify($srcRole) . '_' . $etbDst->_id;
        if($liaisons_operateurs->exist($idLiaison) && $liaisons_operateurs->get($idLiaison)){
          echo "- La liaison existe déjà \n";
          return;
        }
        $etbSrc->addLiaison(KeyInflector::slugify($srcRole),$etbDst);
        $etbSrc->save();
        $compte = CompteClient::getInstance()->find($etbSrc->getCompte());
        $compte->addTag('manuel',$srcRole);
        $compte->save();
        echo "- La liaison a été créé \n";
      }


}
