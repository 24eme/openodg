<?php

class importEtablissementSocieteTelephonesTask extends sfBaseTask
{

    CONST CVI = 0;
    CONST TELEPHONE_FIXE = 6;
    CONST TELEPHONE_MOBILE = 7;


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
        $this->name = 'etablissement-societe-telephones';
        $this->briefDescription = "Import Mineur des téléphones à partir de cvis";
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
        echo "-------------- MAJ ETABLISSEMENTS PRINCIPAUX ---------------\n";
        foreach(file($file_path) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^EVV principal/", $line)) {
                continue;
            }
            $data = str_getcsv($line, ';');
            $etablissement = EtablissementClient::getInstance()->findByCvi($data[self::CVI]);
            if(!$data[self::CVI] || !$etablissement){
              echo 'WARNING: '.$line." CVI : non présent dans la base\n";
              continue;
            }

            $this->majEtbOrSocTelephones($data,$etablissement);

        }
        echo "-------------- MAJ SOCIETES ---------------\n";
        foreach(file($file_path) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^EVV principal/", $line)) {
                continue;
            }
            $data = str_getcsv($line, ';');
            $etablissement = EtablissementClient::getInstance()->findByCvi($data[self::CVI]);
            if(!$data[self::CVI] || !$etablissement){
              echo 'WARNING: '.$line." CVI : non présent dans la base\n";
              continue;
            }

            $this->majEtbOrSocTelephones($data,$etablissement->getSociete());

        }
    }

    protected function formatTel($tel){

        if(!$tel){
            return null;
        }
        $t = str_replace(array(' ','.'),array('',''),$tel);
        if(strlen($t) > 10 && preg_match("/^\+?33/",$t)){
          $t = preg_replace("/^\+?33(.+)/","0$1", $t);
        }
        $tk = sprintf("%010d",$t);
        return substr($tk, 0,2)." ".substr($tk,2,2)." ".substr($tk,4,2)." ".substr($tk,6,2)." ".substr($tk,8,2);
    }

    protected function majEtbOrSocTelephones($data,$etb_or_soc){
            $tel_fixe = $this->formatTel($data[self::TELEPHONE_FIXE]);
            $tel_mobile = $this->formatTel($data[self::TELEPHONE_MOBILE]);
            $old_telBureau = $etb_or_soc->telephone_bureau;
            $old_telMobile = $etb_or_soc->telephone_mobile;

            echo $etb_or_soc->_id.' CVI : '.$data[self::CVI];
            $maj=false;
            if($tel_mobile && !$old_telMobile){
              $etb_or_soc->telephone_mobile = $tel_mobile;
              echo " telMobile [".$old_telMobile."]=>[".$tel_mobile."]";
              $maj=true;
            }elseif($tel_mobile && ($old_telMobile!=$tel_mobile)){
              echo " Pas de mis à jour mobile [".$old_telMobile."] fichier => [".$tel_mobile."]";

            }
            if($tel_fixe && ($tel_fixe != $old_telBureau)){
              $etb_or_soc->telephone_perso = $tel_fixe;
              echo " ajout de telPerso [".$tel_fixe."]";
              $maj=true;
            }

            if(!$maj){
              echo " Aucune mise à jour effectuée";
            }else{
              $etb_or_soc->save();
            }
            echo "\n";
    }

}
