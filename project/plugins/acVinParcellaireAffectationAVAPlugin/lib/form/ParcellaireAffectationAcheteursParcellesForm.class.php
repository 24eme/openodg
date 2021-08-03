<?php

class ParcellaireAffectationAcheteursParcellesForm extends ParcellaireAffectationAcheteursForm {

    public function configure() {
        $produits = $this->getDocument()->declaration->getProduitsWithLieuEditable();
        ksort($produits);

        foreach ($produits as $hash => $cepage) {
            $lieu_affecte = null;
            if($cepage->getConfig()->hasLieuEditable()) {
                $lieu_affecte = self::buildLieuLibelle($cepage, $hash);
            }
            if(!$cepage->isAffectee($lieu_affecte)) {
            	continue;
            }

            if(count($this->getAcheteurs()) < 2){
              continue;
            }
            if(count($cepage->getProduitsCepageDetails()) < 2) {
                continue;
            }

            $this->setWidget($hash, new sfWidgetFormChoice(array('choices' => $this->getAcheteurs(), 'multiple' => true, 'expanded' => true), array('disabled' => 'disabled')));
            $this->setValidator($hash, new sfValidatorChoice(array('choices' => array_keys($this->getAcheteurs()), 'multiple' => true, 'required' => false)));
            $this->getWidget($hash)->setLabel(ParcellaireAffectationAcheteursForm::buildLibelle($cepage, $hash));

            foreach($cepage->getProduitsCepageDetails() as $parcelle) {
                if($lieu_affecte && $parcelle->lieu != $lieu_affecte) {
                    continue;
                }
                $acheteurs = $parcelle->getAcheteursCepageByCVI();

                if(count($acheteurs) < 2) {
                    continue;
                }

                if (! $parcelle->active) {
                    continue;
                }

                $this->setWidget($parcelle->getHash(), new sfWidgetFormChoice(array('choices' => $this->getAcheteurs(), 'multiple' => true, 'expanded' => true)));
                $this->setValidator($parcelle->getHash(), new sfValidatorChoice(array('choices' => array_keys($this->getAcheteurs()), 'multiple' => true, 'required' => false)));
                $this->getWidget($parcelle->getHash())->setLabel("Parcelle n° ". $parcelle->section. "/".$parcelle->numero_parcelle . " à ".$parcelle->commune. " de ".$parcelle->getSuperficie()." ares");
            }
        }

        $this->widgetSchema->setNameFormat('parcellaire_acheteurs_parcelles[%s]');
    }

    public function updateDefaults() {
        parent::updateDefaults();
        $defaults = $this->getDefaults();

        foreach($this->getDocument()->declaration->getProduitsCepageDetails() as $parcelle) {
            $hash = $parcelle->getHash();
            if(!$parcelle->exist('acheteurs')) {
                continue;
            }
            foreach($parcelle->getAcheteursByCVI() as $acheteur) {
                if(!isset($defaults[$hash])) {
                    $defaults[$hash] = array();
                }
                $key = sprintf("/acheteurs/%s/%s", $acheteur->getParent()->getKey(), $acheteur->getKey());
                if(in_array($key, $defaults[$hash])) {
                    continue;
                }
                $defaults[$hash] = array_merge($defaults[$hash], array($key));
            }
        }

        $this->setDefaults($defaults);
    }

    public function update() {
        foreach($this->getDocument()->declaration->getProduitsCepageDetails() as $parcelle) {
            $parcelle->remove('acheteurs');
        }

        foreach($this->values as $hash_parcelle => $hash_acheteurs) {
            if(!preg_match('|/detail/|', $hash_parcelle)) {
                continue;
            }

            if(!$hash_acheteurs) {
                continue;
            }


            foreach($hash_acheteurs as $hash_acheteur) {
                $cvi = preg_replace("|^.+/|", "", $hash_acheteur);
                $parcelle = $this->getDocument()->get($hash_parcelle);
                $parcelle->add('acheteurs');
                $parcelle->acheteurs->add(null, $cvi);
            }
        }
    }

}
