<?php

class ParcellesRienSuccessTask extends sfBaseTask
{
    //php symfony intention-parcelles-success:import /home/moussa_24/Nextcloud/24eme/dpap2019.csv /home/moussa_24/Nextcloud/24eme/dpap2019_rien_find_perfect.csv /home/moussa_24/Nextcloud/24eme/dpap2019_rien_found_in_diff_account.csv --application="provence" --env="preprod"

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_in_out', sfCommandArgument::REQUIRED, "Document name"),
            new sfCommandArgument('input_file_perfect', sfCommandArgument::REQUIRED, "file name of out perfect found"),
            new sfCommandArgument('input_file_diff_account', sfCommandArgument::REQUIRED, "file name of out"),
            
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
        
        $fperfect = $arguments["input_file_perfect"];
        $finout = $arguments["doc_in_out"];
        $fdiff = $arguments['input_file_diff_account'];
        
        $oldDataKeys = [];
        $oldData = $this->csvToArray($finout,",");
        foreach($oldData as $key => $data){
            if($key) $oldDataKeys[$data[0]."-".$data[1]."-".str_replace(" ", "-",$data[3])."-".str_replace(",",".", $data[2])] = $data;
        }

        $perfectData = $this->csvToArray($fperfect, ",");
        foreach($perfectData as $data){
            $perfectDataKeys[$data[0]."-".$data[1]."-".str_replace(" ", "-",$data[3])."-".str_replace(",",".", $data[5])] = $data;

        }
        $diffAccountData = $this->csvToArray($fdiff, ",");
        foreach($diffAccountData as $data){
            $diffAccountDataKeys[$data[0]."-".$data[1]."-".str_replace(" ", "-",$data[3])."-".str_replace(",",".", $data[5])] = $data;
        }
        $fp = fopen($finout, 'w');
        $merge = array_merge($diffAccountDataKeys, $perfectDataKeys);
        $nb = 0;
        foreach ($oldDataKeys as $key => $value) {
            if(in_array($key, array_keys($merge))){
                $nb++;

                $newAccount = explode("-", $merge[$key][2]);
                print_r($newAccount);
                echo sprintf("Success;%s;%s\n", $oldDataKeys[$key][0], $newAccount[1]);

                $oldDataKeys[$key][0] = $newAccount[1];
                fputcsv($fp, $oldDataKeys[$key],";");
            }else{
                fputcsv($fp, $oldDataKeys[$key],";");
                echo sprintf("Warning;non modifie;%s\n", $key);
            }
        }
        fclose($fp);
        echo sprintf("IDU Changed : %s / %s \n", $nb, count($oldData));
    }    
}
