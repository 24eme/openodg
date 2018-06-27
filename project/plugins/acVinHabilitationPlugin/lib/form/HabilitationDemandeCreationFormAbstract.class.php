<?php
abstract class HabilitationDemandeCreationFormAbstract extends HabilitationDemandeEditionForm
{
    public function configure()
    {
        parent::configure();

        $demandes = $this->getDemandes();

        if(count($demandes) == 1) {
            $this->setDefault('demande', key($demandes));
        }

        $this->setWidget('demande', new sfWidgetFormChoice(array('choices' => $demandes)));
        $this->widgetSchema->setLabel('demande', 'Demande: ');
        $this->setValidator('demande',new sfValidatorChoice(array('required' => true, 'choices' => array_keys($demandes))));

        $this->embedForm('donnees', new HabilitationDemandeDonneesProduitForm($this->getDocument()));

        $this->widgetSchema->setNameFormat('habilitation_demande_creation[%s]');
    }

    public function getDemandes(){

        return array_merge(array("" => ""), HabilitationClient::$demande_libelles);
    }

    public function getDonnees() {

        return $this->getValue('donnees');
    }

    public function save()
    {
        if(!count($this->getDonnees())) {
            return;
        }

        $values = $this->getValues();

        $demande = HabilitationClient::getInstance()->createDemandeAndSave(
            $this->getDocument()->identifiant,
            $values['demande'],
            $this->getDonnees(),
            $values['statut'],
            $values['date'],
            $values['commentaire'],
            ""
        );

        return $demande;
    }
}
