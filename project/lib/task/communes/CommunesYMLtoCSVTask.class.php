<?php

class CommunesYMLtoCSVTask extends sfBaseTask
{

    protected function configure(){
      $this->addArguments(array(
            new sfCommandArgument('region', sfCommandArgument::REQUIRED, "region"),
        ));

      $this->namespace = 'communes';
        $this->name = 'export-yml-csv';
        $this->briefDescription = "Export YML communes to CSV";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()){
      $region = $arguments["region"];
      $dir = sfConfig::get('sf_apps_dir');

      $filename= 'communes.yml';
      $delimiter = ';';
      $enclosure = '"';
      $link = $dir."/".$region."/config/".$filename;

      if(is_dir($dir) && is_file($link)){
        if($fp = fopen("/tmp/communes_".$region.".csv", 'w')){
          $parsedArray = sfYaml::load($link);
          $csv = "";
          fputcsv($fp, ["Numéro commune", "Nom commune"], $delimiter, $enclosure);
          foreach ($parsedArray["all"]["configuration"]["communes"] as $num => $commune) {
            fputcsv($fp, [$num, $commune], $delimiter, $enclosure);
          }
          fclose($fp);
        }else{
          print_r("Erreur de création du fichier dans : "."/tmp/communes_".$region.".csv");
        }
        
      }else{
        print_r($region." n'est pas une région.");
      }

     
      
      

        
    }
}