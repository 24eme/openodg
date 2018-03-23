<?php

class importInterlocuteursFromCSVTask extends sfBaseTask
{

    protected $file_path = null;

    const CSV_ID = 0;
    const CSV_TITRE = 1;
    const CSV_NOM = 2;

    const CSV_DIRECTION_NOM = 3;
    const CSV_DIRECTION_TEL_BUREAU = 4;
    const CSV_DIRECTION_TEL_MOBILE = 5;
    const CSV_DIRECTION_EMAIL = 6;

    const CSV_RESPVIGNOBLE_NOM = 7;
    const CSV_RESPVIGNOBLE_TEL_BUREAU = 8;
    const CSV_RESPVIGNOBLE_TEL_MOBILE = 9;
    const CSV_RESPVIGNOBLE_EMAIL = 10;

    const CSV_RESPCAVE_NOM = 11;
    const CSV_RESPCAVE_TEL_BUREAU = 12;
    const CSV_RESPCAVE_TEL_MOBILE = 13;
    const CSV_RESPCAVE_EMAIL = 14;

    const CSV_RESPCOMMERCE_NOM = 15;
    const CSV_RESPCOMMERCE_TEL_BUREAU = 16;
    const CSV_RESPCOMMERCE_TEL_MOBILE = 17;
    const CSV_RESPCOMMERCE_EMAIL = 18;

    const CSV_RESPADMIN_NOM = 19;
    const CSV_RESPADMIN_TEL_BUREAU = 20;
    const CSV_RESPADMIN_TEL_MOBILE = 21;
    const CSV_RESPADMIN_EMAIL = 22;





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
        $this->name = 'interlocuteurs-from-csv';
        $this->briefDescription = "Import des interlocuteurs";
        $this->detailedDescription = <<<EOF
EOF;
        $this->fct_directeur = 'Direction';
        $this->fct_vignoble = 'Vignoble';
        $this->fct_cave = 'Cave';
        $this->fct_commerce = 'Commerce';
        $this->fct_administrateur = 'Administratif';
        $this->civilites = array('MADAME' => "Mme",
                                 'MME' => "Mme",
                                 'MRS' => "Mme",
                                 'MME.' => "Mme",
                                 'MONISEUR' => "M",
                                 'MONSIEUR' => "M",
                                 'MONSIUER' => "M",
                                 'M' => "M",
                                 'M.' => "M",
                                 'MR' => "M");
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $this->file_path = $arguments['file_path'];

        error_reporting(E_ERROR | E_PARSE);

        $this->import();

    }

    protected function import(){

      if(!$this->file_path){
        throw new  sfException("Le paramètre du fichier csv doit être renseigné");

      }
      error_reporting(E_ERROR | E_PARSE);

      foreach(file($this->file_path) as $line) {
          $line = str_replace("\n", "", $line);
          if(preg_match("/IdOp/", $line)) {
              continue;
          }
          $this->importInterlocuteurs($line);
        }
    }

    protected function importInterlocuteurs($line){
            $data = str_getcsv($line, ';');
            if(!preg_match('/^'.SocieteClient::getInstance()->getSocieteFormatIdentifiantRegexp().'$/', $data[self::CSV_ID])) {
                throw new Exception("Mauvais identifiant ". $data[self::CSV_ID]);
            }

            $societe = SocieteClient::getInstance()->find($data[self::CSV_ID]);
            if(!$societe){
                echo "/!\  La societe d'id ".$data[self::CSV_ID]." n'existe pas dans la base => pas d'import\n";
                return;
            }

            $societe = $this->importInterlocuteur($societe, $this->fct_directeur,
                                                  $data[self::CSV_DIRECTION_NOM],
                                                  $data[self::CSV_DIRECTION_TEL_BUREAU],
                                                  $data[self::CSV_DIRECTION_TEL_BUREAU],
                                                  $data[self::CSV_DIRECTION_EMAIL]);

              $societe = $this->importInterlocuteur($societe, $this->fct_vignoble,
                                                    $data[self::CSV_RESPVIGNOBLE_NOM],
                                                    $data[self::CSV_RESPVIGNOBLE_TEL_BUREAU],
                                                    $data[self::CSV_RESPVIGNOBLE_TEL_BUREAU],
                                                    $data[self::CSV_RESPVIGNOBLE_EMAIL]);

              $societe = $this->importInterlocuteur($societe, $this->fct_cave,
                                                    $data[self::CSV_RESPCAVE_NOM],
                                                    $data[self::CSV_RESPCAVE_TEL_BUREAU],
                                                    $data[self::CSV_RESPCAVE_TEL_BUREAU],
                                                    $data[self::CSV_RESPCAVE_EMAIL]);

              $societe = $this->importInterlocuteur($societe, $this->fct_commerce,
                                                    $data[self::CSV_RESPCOMMERCE_NOM],
                                                    $data[self::CSV_RESPCOMMERCE_TEL_BUREAU],
                                                    $data[self::CSV_RESPCOMMERCE_TEL_BUREAU],
                                                    $data[self::CSV_RESPCOMMERCE_EMAIL]);

              $societe = $this->importInterlocuteur($societe, $this->fct_administrateur,
                                                    $data[self::CSV_RESPADMIN_NOM],
                                                    $data[self::CSV_RESPADMIN_TEL_BUREAU],
                                                    $data[self::CSV_RESPADMIN_TEL_BUREAU],
                                                    $data[self::CSV_RESPADMIN_EMAIL]);
    }

    protected function importInterlocuteur($societe,$fct,$nom,$telephone_bureau,$telephone_mobile,$email){
        $n = trim($nom);
        $tb = trim($telephone_bureau);
        $tm = trim($telephone_mobile);
        $e = trim($email);
        if(!$n && !$tb && !$tm && !$e){
        //    echo "La societe ".$societe->_id." n'a pas d'interlocuteur pour la fonction ".$fct." \n";
            return $societe;
        }

        $contact = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);
        $contact->fonction = $fct;


        if(!$contact->nom){
            $contact->nom = "AUTRE CONTACT";
        }
        $contact->telephone_bureau = $this->formatTel($tb);
        $contact->telephone_mobile = $this->formatTel($tm);
        $contact->email = $e;
        echo "La societe ".$societe->_id." a un interlocuteur ".$fct." : ";
        $this->formatNom($n,$contact);
        $contact->addTag('manuel',$fct);
        echo $contact->civilite." ".$contact->prenom." ".$contact->nom." ".$contact->telephone_bureau."/".$contact->telephone_mobile."/".$contact->email." \n";
        $contact->save();
        $societe = SocieteClient::getInstance()->find($societe->_id);

        return $societe;
    }

    protected function formatNom($nom,$contact){
        $matches = array();
        $matches2  = array();
        if(preg_match('/([a-zA-Z]+)[ ]+([A-Z ]{2,3}[A-Z]{2,})[ ]?(.*)/',$nom,$matches)){
            $nom_supp = $matches[2];
            $contact->nom = trim($nom_supp);
            $reste = str_replace(trim($nom_supp),'',$nom);
            if(preg_match('/([a-zA-Z.]+)[ ]+([A-Za-zéèêëàäâöôüûïîç.]+)/',$reste,$matches2)){
                if(array_key_exists(strtoupper($matches2[1]),$this->civilites)){
                    $contact->civilite = $this->civilites[strtoupper($matches2[1])];
                    $contact->prenom = $matches2[2];
                }else{
                    $contact->nom = trim($nom);
                }
            }else{
                if(array_key_exists(strtoupper(trim($reste)),$this->civilites)){
                    $contact->civilite = $this->civilites[strtoupper(trim($reste))];
                }else{
                    $contact->nom = trim($nom);
                }
            }

        }else{
            $contact->nom = trim($nom);
        }
    }

    protected function formatTel($tel){
        if(!$tel){
            return null;
        }
        $t = str_replace(array(' ','.'),array('',''),$tel);
        $tk = sprintf("%010d",$t);
        return substr($tk, 0,2)." ".substr($tk,2,2)." ".substr($tk,4,2)." ".substr($tk,6,2)." ".substr($tk,8,2);
    }


}
