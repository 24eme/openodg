<?php

class DRDouaneCsvFile {

    protected $filePath = null;

    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    public function convert() {
        $handler = fopen($this->filePath, 'r');

        $csv = array();

        while (($data = fgetcsv($handler)) !== FALSE) {
            $csv[] = self::clean($data);
        }

        $cvi = $csv[1][1];
        $raisonSociale = trim(preg_replace('/^(.+)\(.+\)$/', '\1', $csv[1][2]));
        $commune = trim(preg_replace('/^.+\((.+)\)$/', '\1', $csv[1][2]));
        $produits = array();

        $j = -1;
        foreach($csv as $data) {
            $j++;
            if(!isset($data[1])) {
                continue;
            }
            for($i = 3; $i < count($data); $i = $i + 2) {
                $numColonne = $i - 2;
                if(!isset($produits[$numColonne])) {
                    $produits[$numColonne] = array("L06_L08_vente" => array());
                }

                if($data[1] == "Code produit") {
                    $produits[$numColonne]["L01_code_douane"] = $data[$i];
                }
                if($data[1] == "Libelle produit") {
                    $produits[$numColonne]["L00_libelle"] = $data[$i];
                }
                if($data[1] == "Mention valorisante") {
                    $produits[$numColonne]["L02_mention_valorisante"] = $data[$i];
                }
                if($data[1] == "Zone viticole de récolte") {
                    $produits[$numColonne]["L03_zone_viticole_recolte"] = $data[$i];
                }
                if($data[1] == "Superficie de récolte (Ha)") {
                    $produits[$numColonne]["L04_superficie"] = $data[$i]*100;
                }
                if($data[1] == "Récolte totale") {
                    $produits[$numColonne]["L05_recolte_totale"] = $csv[$j + 1][$i];
                }
                if(!preg_match("/[6-8]{1}-0/", $data[0]) && preg_match("/[6-8]{1}-[0-9]+/", $data[0]) && $data[$i]) {
                    $vente = array();
                    $vente["numero"] = $data[0];
                    $vente["type"] = "";
                    $vente["cvi"] = preg_replace("/^Acheteur n°([0-9]+) - .+$/", '\1', $data[1]);
                    $vente["raisonSociale"] = preg_replace("/^Acheteur n°[0-9]+ - (.+)$/", '\1', $data[1]);
                    $vente["volume"] = $data[$i];
                    $produits[$numColonne]["L06_L08_vente"][] = $vente;
                }
                if($data[1] == "Récolte en cave particulière. Volume obtenu") {
                    $produits[$numColonne]["L09_cave_particuliere"] = $data[$i];
                }
                if($data[1] == "Vol. vin dépassement du rdt autorisé en AOP à livrer aux usages industriels") {
                    $produits[$numColonne]["L16_usages_industriels"] = $data[$i];
                }
                if($data[1] == "Volume complémentaire individuel (VCI)") {
                    $produits[$numColonne]["L19_vci"] = $data[$i];
                }
            }
        }
        $csvFinal = "";
        foreach($produits as $produit) {
            if ($produit["L09_cave_particuliere"]) {
                $csvFinal .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                    $cvi,
                    "SUR PLACE",
                    $cvi,
                    $raisonSociale,
                    $produit["L01_code_douane"],
                    $produit["L00_libelle"],
                    null,
                    null,
                    $produit["L02_mention_valorisante"],
                    ($produit["L09_cave_particuliere"] == $produit["L05_recolte_totale"]) ? $produit["L04_superficie"] : null,
                    $produit["L09_cave_particuliere"],
                    ($produit["L09_cave_particuliere"] == $produit["L05_recolte_totale"]) ? $produit["L16_usages_industriels"] : null,
                    $produit["L04_superficie"],
                    $produit["L05_recolte_totale"],
                    $produit["L16_usages_industriels"],
                    $produit["L19_vci"]
                );
            }

            foreach($produit["L06_L08_vente"] as $vente) {
                $csvFinal .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                    $vente["cvi"],
                    $vente["raisonSociale"],
                    $cvi,
                    $raisonSociale,
                    $produit["L01_code_douane"],
                    $produit["L00_libelle"],
                    null,
                    null,
                    $produit["L02_mention_valorisante"],
                    ($vente["volume"] == $produit["L05_recolte_totale"]) ? $produit["L04_superficie"] : null,
                    $vente["volume"],
                    ($vente["volume"] == $produit["L05_recolte_totale"]) ? $produit["L16_usages_industriels"] : null,
                    $produit["L04_superficie"],
                    $produit["L05_recolte_totale"],
                    $produit["L16_usages_industriels"],
                    $produit["L19_vci"]
                );
            }
        }
        return $csvFinal;
    }



    public static function clean($array) {
      for($i = 0 ; $i < count($array) ; $i++) {
        $array[$i] = preg_replace("/[ ]+/", " ", preg_replace('/^ +/', '', preg_replace('/ +$/', '', $array[$i])));
      }
      return $array;
    }


}
