<?php
/**
 * Model for TravauxMarcFournisseur
 *
 */

class TravauxMarcFournisseur extends BaseTravauxMarcFournisseur {

    public function getDateLivraisonFr() {
        $date = $this->getDateLivraisonObject();

        if (!$date) {

            return null;
        }

        return $date->format('d/m/Y');
    }

    public function getDateLivraisonObject() {
        if (!$this->date_livraison) {

            return null;
        }

        return new DateTime($this->date_livraison);
    }

}
