<?php

class exportChaisCsvTask extends sfBaseTask
{

    protected $chais = array();

    protected function configure()
    {
        $this->addArguments(array(
        ));

        $this->addOptions(array(
            new sfCommandOption('without-liaisons', null, sfCommandOption::PARAMETER_REQUIRED, 'Sans liaisons'),
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'export';
        $this->name = 'chais-csv';
        $this->briefDescription = "Export csv des établissements";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $results = EtablissementClient::getInstance()->findAll();

        $withoutLiaisons = (isset($options['without-liaisons']) && $options['without-liaisons']);
        echo "Identifiant chais,Identifiant établissement,Type,Chais Activites,Adresse 1,Adresse 2,Adresse 3,Code postal,Commune,Nom Contact,Tèl Contact, Carte,Position,Archivé,IdCIVP,EA1,EA2,SIRET\n";
$cpt = 0;

        if(!$withoutLiaisons){
            foreach($results->rows as $row) {
                $etablissement = EtablissementClient::getInstance()->find($row->id, acCouchdbClient::HYDRATE_JSON);
                if(isset($etablissement->chais)){
                    foreach($etablissement->chais as $numChai => $chai) {
                        $this->chais[$etablissement->_id.'/chais/'.$numChai] = $chai;
                    }
                }
            }
        }

        foreach($results->rows as $row) {
            $etablissement = EtablissementClient::getInstance()->find($row->id, acCouchdbClient::HYDRATE_JSON);
            if(isset($etablissement->chais)){
                foreach($etablissement->chais as $numChai => $chai) {
                    $activites = array();
                    foreach($chai->attributs as $a) {
                        $activites[] = $a;
                    }
                    sort($activites);
                    $activites = implode(";", $activites);

                    $adresses = explode(' − ', str_replace(array('"',','),array('',''),$chai->adresse));
                    $a_comp = (isset($adresses[1]))? $adresses[1] : "";
                    $a_comp1 = (isset($adresses[2]))? $adresses[2] : "";


                    echo str_replace("ETABLISSEMENT-","",$etablissement->_id."/".$numChai).",".
                    str_replace("ETABLISSEMENT-","",$etablissement->_id).",".
                    "AUTRE,".
                    $activites.",".
                    trim(str_replace('"', '', $adresses[0])).",".
                    trim(str_replace('"', '', $a_comp)).",".
                    trim(str_replace('"', '', $a_comp1)).",".
                    $chai->code_postal.",".
                    $chai->commune.",".
                    $etablissement->raison_sociale.",".
                    $etablissement->telephone_bureau.",".
                    ",,Faux,,,,,\n";
                    }
                }
                if(!$withoutLiaisons && isset($etablissement->liaisons_operateurs)){
                    foreach($etablissement->liaisons_operateurs as $numLiaison => $liaison) {
                        if(!isset($liaison->hash_chai) || !$liaison->hash_chai){
                            continue;
                        }
                        $keyL = $liaison->id_etablissement.$liaison->hash_chai;
                        if(!array_key_exists($keyL,$this->chais)){
                            throw new sfException("Le chai $keyL n'a pas été réfenrencé");
                        }

                        $chaiDistant = $this->chais[$keyL];

                        $activites = array();
                        foreach($chaiDistant->attributs as $a) {
                            $activites[] = $a;
                        }
                        sort($activites);
                        $activites = implode(";", $activites);

                        $attributs = array();
                        foreach($liaison->attributs_chai as $attribut) {
                            $attributs[] = $attribut;
                        }
                        $adresses = explode(' − ', str_replace(array('"',','),array('',''),$chai->adresse));
                        $a_comp = (isset($adresses[1]))? $adresses[1] : "";
                        $a_comp1 = (isset($adresses[2]))? $adresses[2] : "";

                        sort($attributs);
                        $attributs = implode(";", $attributs);

                        $adresses = explode(' − ', str_replace(array('"',','),array('',''),$chaiDistant->adresse));
                        $a_comp = (isset($adresses[1]))? $adresses[1] : "";
                        $a_comp1 = (isset($adresses[2]))? $adresses[2] : "";

                        echo str_replace("ETABLISSEMENT-","",str_replace("/chais/","/",$keyL)).",".
                        str_replace("ETABLISSEMENT-","",$etablissement->_id).",".
                        $attributs.",".
                        $activites.",".
                        trim(str_replace('"', '', $adresses[0])).",".
                        trim(str_replace('"', '', $a_comp)).",".
                        trim(str_replace('"', '', $a_comp1)).",".
                        $chaiDistant->code_postal.",".
                        $chaiDistant->commune.",".
                        $etablissement->raison_sociale.",".
                        $etablissement->telephone_bureau.",".
                        ",,Faux,,,,,\n";
                        }
                }
        }
    }
}
