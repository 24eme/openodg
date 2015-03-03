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
        $this->date_prelevement_fin = $date;

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

            $operateurs[$operateur->getKey()] = $operateur;
        }

        return $operateurs;
    }

    public function storeEtape($etape) {
        if ($etape == $this->etape) {

            return false;
        }

        $this->add('etape', $etape);

        return true;
    }

}
