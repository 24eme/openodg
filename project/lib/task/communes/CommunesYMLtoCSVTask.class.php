<?php

class CommunesYMLtoCSVTask extends sfBaseTask
{

    protected function configure(){
      $this->namespace = 'communes';
        $this->name = 'export-yml-csv';
        $this->briefDescription = "Export YML communes to CSV";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()){
      $dir = sfConfig::get('sf_apps_dir');

      $filename= 'communes.yml';
      $delimiter = ';';
      $enclosure = '"';
      $csv = [];
      if($fp = fopen($dir."/../data/communes.csv", 'w')){
        //fputcsv($fp, ["Numéro commune", "Nom commune"], $delimiter, $enclosure);
        foreach(scandir($dir, 1) as $region){
          $link = $dir."/".$region."/config/".$filename;
          if(is_file($link)){
            $parsedArray = sfYaml::load($link);
            print_r($region ." \n");
            foreach ($parsedArray["all"]["configuration"]["communes"] as $num => $commune){
                print_r("\t $num, $commune \n");
                fputcsv($fp, [$num, $commune], $delimiter, $enclosure);
            }
            print_r("\n");
          }
        }
        print_r("File output : ".$dir."/../data/communes.csv\n\n");
        fclose($fp);
      }else{
        print_r("Erreur de création du fichier dans : "."/tmp/communes_".$region.".csv");
      }
    
    }
}