<?php

class ParcellaireAcheteursParcellesForm extends ParcellaireAcheteursForm {

    public function configure() {

        $produits = $this->getDocument()->declaration->getProduitsWithLieuEditable();
        ksort($produits);

        foreach ($produits as $cepage) {
            $this->setWidget($cepage->getHash(), new sfWidgetFormChoice(array('choices' => $this->getAcheteurs(), 'multiple' => true, 'expanded' => true), array('disabled' => 'disabled')));
            $this->setValidator($cepage->getHash(), new sfValidatorChoice(array('choices' => array_keys($this->getAcheteurs()), 'multiple' => true, 'required' => false)));
            $this->getWidget($cepage->getHash())->setLabel(ParcellaireAcheteursForm::buildLibelle($cepage));

            $nbParcelles = 0;
            foreach($cepage->getProduitsCepageDetails() as $parcelle) {
                $acheteurs = $parcelle->getAcheteursByCVI();

                if(count($acheteurs) < 2) {
                    continue;
                }

                $this->setWidget($parcelle->getHash(), new sfWidgetFormChoice(array('choices' => $this->getAcheteurs(), 'multiple' => true, 'expanded' => true)));
                $this->setValidator($parcelle->getHash(), new sfValidatorChoice(array('choices' => array_keys($this->getAcheteurs()), 'multiple' => true, 'required' => false)));
                $this->getWidget($parcelle->getHash())->setLabel("Parcelle n° ". $parcelle->section. "/".$parcelle->numero_parcelle . " à ".$parcelle->commune. " de ".$parcelle->getSuperficie()." ares");
                $nbParcelles++;
            }

            if(!$nbParcelles) {
                unset($this->widgetSchema[$cepage->getHash()]);
                unset($this->validatorSchema[$cepage->getHash()]);
            }
        }

        $this->widgetSchema->setNameFormat('parcellaire_acheteurs_parcelles[%s]');
    }

    public function updateDefaults() {
        parent::updateDefaults();

        $defaults = $this->getDefaults();
        $this->setDefaults($defaults);
    }

    public function update() {

    }

}
