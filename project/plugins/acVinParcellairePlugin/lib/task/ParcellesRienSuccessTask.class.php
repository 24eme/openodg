<?php

class ParcellesRienSuccessTask extends sfBaseTask
{
    //php symfony intention-parcelles-success:import /home/moussa_24/Nextcloud/24eme/dpap2019.csv /home/moussa_24/Nextcloud/24eme/dpap2019_rien_find_perfect.csv /home/moussa_24/Nextcloud/24eme/dpap2019_perfect.csv --application="provence" --env="preprod" > ~/log_import

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_name', sfCommandArgument::REQUIRED, "Document name"),
            new sfCommandArgument('outputfileperfect', sfCommandArgument::REQUIRED, "file name of out perfect found"),
            new sfCommandArgument('outputfile', sfCommandArgument::REQUIRED, "file name of out"),
            
        ));
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'intention-parcelles-success';
        $this->name = 'import';
        $this->briefDescription = "Import de parcelles non trouvées";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function csvToArray($filename, $delimiter=";"){
        $content = [];
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $content[] = $data;
            }
        }

        fclose($handle);
        return $content;
    }

    protected function execute($arguments = array(), $options = array())
    {   
        
        $fperfect = $arguments["outputfileperfect"];
        $filename = $arguments["doc_name"];
        
        $oldDataKeys = [];
        $oldData = $this->csvToArray($filename);
        foreach($oldData as $data){
            $oldDataKeys[$data[0]."-".$data[1]."-".str_replace(" ", "-",$data[3])."-".str_replace(",",".", $data[2])] = $data;
        }
        $perfectData = $this->csvToArray($fperfect, ",");
        foreach($perfectData as $data){
            $perfectDataKeys[$data[0]."-".$data[1]."-".str_replace(" ", "-",$data[3])."-".str_replace(",",".", $data[4])] = $data;
        }
        $fp = fopen($arguments["outputfile"], 'w');
        foreach ($perfectDataKeys as $key => $value) {
            if(in_array($key, array_keys($oldDataKeys))){
                $newAccount = explode("-", $value[2]);
                echo sprintf("Success;%s;%s\n", $oldDataKeys[$key][0], $newAccount);
                $oldDataKeys[$key][0] = $newAccount[1];
                fputcsv($fp, $oldDataKeys[$key]);
            }else{
                echo sprintf("Error;%s\n", $key);
            }
        }
        fclose($fp);
    }
    
}
