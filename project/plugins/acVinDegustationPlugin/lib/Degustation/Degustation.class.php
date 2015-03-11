<?php

/**
 * Model for Degustation
 *
 */
class Degustation extends BaseDegustation {

    public function constructId() {
        $this->identifiant = sprintf("%s-%s", str_replace("-", "", $this->date), $this->appellation);
        $this->set('_id', sprintf("%s-%s", DegustationClient::TYPE_COUCHDB, $this->identifiant));
    }

    public function getConfiguration() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration("2014");
    }

    public function setDate($date) {
        $dateObject = new DateTime($date);
        $this->date_prelevement_fin = $dateObject->modify("-5 days")->format('Y-m-d');

        return $this->_set('date', $date);
    }

    public function getProduits() {

        return $this->getConfiguration()->declaration->certification->genre->appellation_ALSACE->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
    }

    public function getOperateursOrderByHour() {
        $operateurs = array();
        foreach ($this->operateurs as $operateur) {
            $heure = $operateur->heure;

            if (!$operateur->heure) {
                $heure = "24:00";
            }
            $operateurs[$heure][sprintf('%05d', $operateur->position).$operateur->getKey()] = $operateur;
            ksort($operateurs[$heure]);
        }

        return $operateurs;
    }

    public function getTournees() {
        $tournees = array();
        foreach ($this->operateurs as $operateur) {
            if (!$operateur->date) {
                continue;
            }

            if (!$operateur->agent) {
                continue;
            }
            if (!array_key_exists($operateur->date . $operateur->agent, $tournees)) {
                $tournees[$operateur->date . $operateur->agent] = new stdClass();
                $tournees[$operateur->date . $operateur->agent]->operateurs = array();
                $agents = $this->agents->toArray();
                $tournees[$operateur->date . $operateur->agent]->id_agent = $operateur->agent;
                $tournees[$operateur->date . $operateur->agent]->nom_agent = $agents[$operateur->agent]->nom;
                $tournees[$operateur->date . $operateur->agent]->date = $operateur->date;
            }
            $tournees[$operateur->date . $operateur->agent]->operateurs[$operateur->getKey()] = $operateur;
        }
        ksort($tournees);
        return $tournees;
    }

    public function getTourneeOperateurs($agent, $date) {
        $operateurs = array();
        foreach ($this->operateurs as $operateur) {
            if ($operateur->agent != $agent) {

                continue;
            }

            if ($operateur->date != $date) {

                continue;
            }

            $operateurs[sprintf('%05d', $operateur->position) . $operateur->getKey()] = $operateur;
        }

        ksort($operateurs);

        return $operateurs;
    }

    public function cleanPrelevements() {
        $hash_to_delete = array();

        foreach($this->operateurs as $operateur) {
            foreach($operateur->prelevements as $prelevement) {
                if($prelevement->preleve && $prelevement->cuve && $prelevement->hash_produit) {
                    continue;
                }
                $hash_to_delete[$prelevement->getHash()] = $prelevement->getHash();
            }
        }

        krsort($hash_to_delete);

        foreach($hash_to_delete as $hash) {
            $this->remove($hash);
        }
    }

    public function getPrelevementsByNumeroPrelevement() {
        $prelevements = array();

        foreach($this->operateurs as $operateur) {
            foreach($operateur->prelevements as $prelevement) {
                $prelevements[$prelevement->anonymat_prelevement] = $prelevement;
            }
        }

        return $prelevements;
    }

    public function getPrelevementsByNumeroDegustation($commission) {
        $prelevements = array();

        foreach($this->operateurs as $operateur) {
            foreach($operateur->prelevements as $prelevement) {
                if($prelevement->commission != $commission) {
                    continue;
                }
                $cepage_key = substr($prelevement->hash_produit, -2);
                $prelevements["P".DegustationClient::$ordre_cepages[$cepage_key].sprintf("%03d", $prelevement->anonymat_degustation)] = $prelevement;
            }
        }

        ksort($prelevements);

        $prelevements_return = array();

        foreach($prelevements as $prelevement) {
            $prelevements_return[$prelevement->anonymat_degustation] = $prelevement;
        }

        return $prelevements;
    }

    public function generateNumeroDegustation() {
        $prelevements = $this->getPrelevementsByNumeroPrelevement();
        shuffle($prelevements);

        $i = 1;
        foreach($prelevements as $prelevement) {
            $prelevement->anonymat_degustation = $i;
            foreach(DegustationClient::$note_type_libelles as $key_type_note => $libelle_type_note) {
                $prelevement->notes->add($key_type_note);
            }
            $i++;
        }
    }

    public function generatePrelevements() {
        $j = 10;

        foreach($this->operateurs as $operateur) {
            if(count($operateur->prelevements) > 0) {
                return false;
            }
        }

        foreach ($this->operateurs as $operateur) {
            $operateur->cvi = $operateur->getKey();
            $compte = CompteClient::getInstance()->findByIdentifiant("E" . $operateur->cvi);
            $operateur->telephone_bureau = $compte->telephone_bureau;
            $operateur->telephone_prive = $compte->telephone_prive;
            $operateur->telephone_mobile = $compte->telephone_mobile;
            foreach($operateur->lots as $lot) {
                for($i=0; $i < $lot->nb; $i++) {
                    $prelevement = $operateur->prelevements->add();
                    $prelevement->hash_produit = $lot->hash_produit;
                    $prelevement->libelle = $lot->libelle;
                    $code_cepage = substr($lot->hash_produit, -2);
                    $prelevement->anonymat_prelevement = sprintf("%s%03d%02X", $code_cepage, $j, $j);
                    $prelevement->preleve = 1;
                    $j++;
                }
                for($i=1; $i <= 2; $i++) {
                    $prelevement = $operateur->prelevements->add();
                    $prelevement->anonymat_prelevement = sprintf("%s%03d%02X", "__", $j, $j);
                    $prelevement->preleve = 0;
                    $j++;
                }
            }
        }

        return true;
    }

    public function storeEtape($etape) {
        if ($etape == $this->etape) {

            return false;
        }

        $this->add('etape', $etape);

        return true;
    }

}
