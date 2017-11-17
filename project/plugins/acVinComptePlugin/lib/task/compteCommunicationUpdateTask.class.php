<?php

class compteCommunicationUpdateTask extends sfBaseTask
{
  const COM_ID = 0;
  const COM_NUM = 1;
  const COM_NOM = 2;
  const COM_EMAIL = 3;
  const COM_FAX = 4;
  const COM_PORTABLE = 5;
  const COM_TEL = 6;
  const COM_SITEWEB = 7;


    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file_path', sfCommandArgument::REQUIRED, "Fichier csv pour l'import")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'compte';
        $this->name = 'communications-update';
        $this->briefDescription = "Fixe du numéro d'archivage des comptes";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $file_path = $arguments['file_path'];

        if(!$file_path){
          throw new  sfException("Le paramètre du fichier csv doit être renseigné");

        }

        foreach(file($file_path) as $line) {
            $data = split(';', $line);
             try{
                $this->compteCommunicationUpdate($data);
             } catch (Exception $e) {
                 echo sprintf("ERROR;%s;#LINE;%s\n", $e->getMessage(), $line);
                 continue;
             }
        }

    }

    protected function compteCommunicationUpdate($data){
      //A TRAITER
      $societe = SocieteClient::getInstance()->find('SOCIETE-'.sprintf("%06d",$data[self::COM_ID]));
      if(!$societe){
        echo  $data[self::COM_ID]." société non trouvée\n";
        return;
      }
      $compte = $societe->getMasterCompte();
      if(!$compte){
        echo  $data[self::COM_ID]." société ".$societe->_id." n'a pas de compte\n";
        return;
      }
      $compte->societe_informations->email = $data[self::COM_EMAIL];
      $compte->societe_informations->telephone = $data[self::COM_TEL];
      $compte->societe_informations->fax = $data[self::COM_FAX];

      $compte->email = $data[self::COM_EMAIL];
      $compte->fax = $data[self::COM_FAX];
      $compte->telephone_bureau = $data[self::COM_TEL];
      $compte->telephone_mobile = $data[self::COM_PORTABLE];
      $compte->site_internet = $data[self::COM_SITEWEB];
      $compte->save();

      $societe->email = $data[self::COM_EMAIL];
      $societe->telephone = $data[self::COM_TEL];
      $societe->fax = $data[self::COM_FAX];
      $societe->save();

      echo $data[self::COM_ID]." société ".$societe->_id." updated :".implode(",",$data);
    }


}
