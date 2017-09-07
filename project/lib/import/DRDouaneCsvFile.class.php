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
                if(preg_match('/code produit/i', $data[1])) {
                    $produits[$numColonne]["L01_code_douane"] = $data[$i];
                }
                if(preg_match('/libelle produit/i', $data[1])) {
                    $produits[$numColonne]["L00_libelle"] = $data[$i];
                }
                if(preg_match('/mention valorisante/i', $data[1])) {
                    $produits[$numColonne]["L02_mention_valorisante"] = $data[$i];
                }
                if(preg_match('/zone viticole de/i', $data[1])) {
                    $produits[$numColonne]["L03_zone_viticole_recolte"] = $data[$i];
                }
                if(preg_match('/superficie de/i', $data[1])) {
                    $produits[$numColonne]["L04_superficie"] = $data[$i]*100;
                }
                if(preg_match('/colte totale/i', $data[1])) {
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
                if(preg_match('/colte en cave particuli/i', $data[1])) {
                    $produits[$numColonne]["L09_cave_particuliere"] = $data[$i];
                }
                if(preg_match('/vol. de vin avec AO\/IGP avec\/sans c/i', $data[1])) {
                    $produits[$numColonne]["L15_recolte_nette"] = $data[$i];
                }
                if(preg_match('/livrer aux usages industriels/i', $data[1])) {
                    $produits[$numColonne]["L16_usages_industriels"] = $data[$i];
                }
                if(preg_match('/VCI/', $data[1])) {
                    $produits[$numColonne]["L19_vci"] = $data[$i];
                }
            }
        }
        $csvFinal = "";
        foreach($produits as $produit) {
            if ($produit["L09_cave_particuliere"]) {
                $csvFinal .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
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
                    $produit["L15_recolte_nette"],
                    $produit["L19_vci"]
                );
            }

            foreach($produit["L06_L08_vente"] as $vente) {
                $csvFinal .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
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
                    $produit["L15_recolte_nette"],
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
