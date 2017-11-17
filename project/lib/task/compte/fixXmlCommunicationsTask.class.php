<?php

class fixXmlCommunicationsTask extends sfBaseTask
{

  const COM_EMAIL = 0;
  const COM_FAX = 1;
  const COM_NUM = 2;
  const COM_PORTABLE = 3;
  const COM_SITEWEB = 4;
  const COM_TEL = 5;
  const COM_TYPECONTACT = 6;

  protected static $communicationsKeys = array(self::COM_EMAIL => "b:Email",
                                    self::COM_FAX => "b:Fax",
                                    self::COM_NUM => "b:NumeroCommunication",
                                    self::COM_PORTABLE => "b:Portable",
                                    self::COM_SITEWEB => "b:SiteWeb",
                                    self::COM_TEL => "b:Telephone",
                                    self::COM_TYPECONTACT => "b:TypeContact");

 protected $file_path;
    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file_path', sfCommandArgument::REQUIRED, "Fichier xml pour l'import")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'xml-communications';
        $this->briefDescription = "Fixe du numéro d'archivage des comptes";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $this->file_path = $arguments['file_path'];
        $file_path = $arguments['file_path'];

        error_reporting(E_ERROR | E_PARSE);
        $this->initLoad($file_path);

        $identifiant = $this->arrayXML["b:CleIdentite"];
        $this->updateCommunications($identifiant);
    }

    protected function initLoad($path){
        $xml_content_str = file_get_contents($path);
        $xml = simplexml_load_string($xml_content_str, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $this->arrayXML = json_decode($json,TRUE);
        if(!$this->arrayXML || !count($this->arrayXML)){
          echo "L'xml de path $path n'a pas été intégré\n";
        }
    }

    protected function updateCommunications($identifiant){
       $communications = $this->getCommunicationsInArr($this->arrayXML['b:Communications']['b:Identite_Communication'],$identifiant);
       if(!$communications){
         echo $this->file_path." : Aucune clé communication\n";
         return;
       }

       foreach ($communications as $key => $communication) {
         echo $this->file_path.";".$communication[self::COM_TYPECONTACT].";".$communication[self::COM_EMAIL].";".$communication[self::COM_FAX].";".$communication[self::COM_PORTABLE].";".$communication[self::COM_TEL].";".$communication[self::COM_SITEWEB].";".$communication[self::COM_NUM]."\n";

       }
    }

    protected function getCommunicationsInArr($arr, $identifiant){
      $communications = array();
      if(isset($arr['b:CleCommunication'])){
        $this->buildCommunicationArr($arr,$communications);
      }else{
        foreach ($arr as $key => $communicationArr) {
          if(isset($communicationArr['b:CleCommunication'])){
            $com = array();
            $this->buildCommunicationArr($communicationArr,$com);
            $communications[] = $com;
          }
        }
      }
      return $communications;
    }

    private function buildCommunicationArr($arr,&$communications){
      foreach (self::$communicationsKeys as $k => $keyName) {
        if(array_key_exists($keyName,$arr)){
          if(is_array($arr[$keyName])){
            $communications[$k] = explode(",",$arr[$keyName]);
          }else{
            $communications[$k] = ($arr[$keyName] !== '__.__.__.__.__')? $arr[$keyName] : "";
          }
        }
      }
    }
}
