<?php

class importRepreneursEtablissementsTask extends sfBaseTask
{

    CONST ID_SRC = 0;
    CONST ID_DEST = 1;


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
        $this->name = 'repreneurs';
        $this->briefDescription = "Création des repreneurs avec suspension des repris";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $routing = clone ProjectConfiguration::getAppRouting();
        $contextInstance = sfContext::createInstance($this->configuration);
        $contextInstance->set('routing', $routing);

        $file_path = $arguments['file_path'];
        if(!$file_path){
          throw new  sfException("Le paramètre du fichier csv doit être renseigné");

        }
        error_reporting(E_ERROR | E_PARSE);

        foreach(file($file_path) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^(id repris;|;;;;;)/", $line)) {
                continue;
            }

            $data = str_getcsv($line, ';');

            try{
                $this->importRepreneursLine($data);
            } catch (Exception $e) {

                echo sprintf("ERROR;%s;#LINE;%s\n", $e->getMessage(), $line);
                $doc = null;
                continue;
            }
        }
    }

    protected function importRepreneursLine($dataLine){
      if($dataLine[self::ID_SRC] && $dataLine[self::ID_DEST]){

        $idRepris = "SOCIETE-".sprintf("%06d",$dataLine[self::ID_SRC]);
        $idRepreneur = "SOCIETE-".sprintf("%06d",$dataLine[self::ID_DEST]);

        $socReprise = SocieteClient::getInstance()->find($idRepris);
        $socRepreneur = SocieteClient::getInstance()->find($idRepreneur);
        if(!$socReprise){
          echo "/!\ La societe reprise d'id $idRepris n'existe pas dans la base. ";
        }
        if(!$socRepreneur){
          echo "/!\ La societe repreneur d'id $idRepreneur n'existe pas dans la base on ne fait rien \n";
          return;
        }
        $etbRepris = null;
        $etbRepreneur = null;

        $etablissementSocRepris = array();
        if($socReprise){
          $etablissementSocRepris = $socReprise->getEtablissementsObj();
        }
        $etablissementSocRepreneuse = $socRepreneur->getEtablissementsObj();

        if(count($etablissementSocRepris)){
          echo "La societe reprise d'id $idRepris a plusieurs etablissements la base on va choisir le premier etb\n";
          foreach ($etablissementSocRepris as $etb) {
            $etbRepris = $etb->etablissement;
            break;
          }
        }

        if(count($etablissementSocRepreneuse)){
            echo "La societe repreneur d'id $idRepreneur a plusieurs etablissements la base  on va choisir le premier etb \n";
            foreach ($etablissementSocRepreneuse as $etb) {
              $etbRepreneur = $etb->etablissement;
              break;
            }
        }

        if(!count($etablissementSocRepris)){
          echo "La societe reprise d'id $idRepris n'a aucun etablissement la base. On va quand même mettre un lien vers la société \n";
        }

        if(!count($etablissementSocRepreneuse)){
          echo "La societe repreneur d'id $idRepreneur n'a aucun etablissement la base création d'un etablissement \n";
          $etbRepreneur = $socRepreneur->createEtablissement( EtablissementFamilles::FAMILLE_PRODUCTEUR);
          $etbRepreneur->nom = $socRepreneur->getRaisonSociale();
        }



        if($etbRepreneur){
          $lienRepreneur =  "<a href=\"".sfContext::getInstance()->getRouting()->generate('etablissement_visualisation',$etbRepreneur)."\" data-relative=\"true\">".$etbRepreneur->nom." (".$etbRepreneur->identifiant.")</a>";

          if($etbRepris){
            $lien =  "<a href=\"".sfContext::getInstance()->getRouting()->generate('etablissement_visualisation',$etbRepris)."\" data-relative=\"true\">".$etbRepris->nom." (".$etbRepris->identifiant.")</a>";

            $etbRepreneur->addCommentaire("Repreneur de l'établissement ".$lien."");
            $etbRepreneur->save();


            $etbRepris->setStatut(SocieteClient::STATUT_SUSPENDU);
            $etbRepris->addCommentaire("Repris par l'établissement ".$lienRepreneur."");
            $etbRepris->save();

            $etbCompte = $etbRepris->getMasterCompte();
            $etbCompte->setStatut(SocieteClient::STATUT_SUSPENDU);
            $etbCompte->save();

            $socReprise->setStatut(SocieteClient::STATUT_SUSPENDU);
            $socReprise->save();

            $socCompte = $socReprise->getMasterCompte();
            $socCompte->setStatut(SocieteClient::STATUT_SUSPENDU);
            $socCompte->save();

          }elseif($socReprise){
            $lien =  "<a href=\"".sfContext::getInstance()->getRouting()->generate('societe_visualisation',$socReprise)."\" data-relative=\"true\">".$socReprise->raison_sociale." (".$socReprise->identifiant.")</a>";
            $etbRepreneur->addCommentaire("Repreneur de la societe ".$lien."");
            $etbRepreneur->save();

            $socReprise->setStatut(SocieteClient::STATUT_SUSPENDU);
            $socReprise->save();
            $socCompte = $socReprise->getMasterCompte();
            $socCompte->setStatut(SocieteClient::STATUT_SUSPENDU);
            $socCompte->addCommentaire("Repris par l'établissement ".$lienRepreneur."");
            $socCompte->save();
          }

          echo "Lien créée \n";
        }else{
          echo "\n";
        }
        return;

      }
    }
}
