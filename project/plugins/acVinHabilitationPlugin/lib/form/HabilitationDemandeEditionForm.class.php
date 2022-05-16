<?php
class HabilitationDemandeEditionForm extends acCouchdbForm
{
    protected $demande = null;

    public function __construct($doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        if($doc instanceof HabilitationDemande) {
            $this->demande = $doc;
            $doc = $doc->getDocument();
            $defaults['activites'] = $this->demande->activites->toArray(true, false);
        }

        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function configure()
    {
        $statuts = $this->getStatuts();

        $this->setWidgets(array(
            'date' => new sfWidgetFormInput(array(), array()),
            'statut' => new sfWidgetFormChoice(array('choices' => $statuts)),
            'commentaire' => new sfWidgetFormInput(array(), array()),
        ));
        $this->widgetSchema->setLabels(array(
            'date' => 'Date: ',
            'statut' => 'Statut: ',
            'commentaire' => 'Commentaire: ',
        ));

        $this->setValidators(array(
            'date' => new sfValidatorDate(
                array('date_output' => 'Y-m-d',
                'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
                'required' => true,
                'max' => date("Y-m-d")),array('max' => 'La date doit être inférieure à la date du jour ('.date('d/m/Y').')')),
            'statut' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($statuts))),
            'commentaire' => new sfValidatorString(array("required" => false)),
        ));

        if(sfContext::getInstance()->getUser()->hasCredential(AppUser::CREDENTIAL_HABILITATION)) {
            $this->setWidget('activites', new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $this->getActivites())));
            $this->getWidget('activites')->setLabel('Activités');
            $this->setValidator('activites', new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($this->getActivites()))));
        }

        $this->widgetSchema->setNameFormat('habilitation_demande_edition[%s]');
    }

    public function getActivites() {

        if(!$this->demande) {
            return array();
        }

        return $this->demande->getActivitesLibelle();
    }

    public function getStatuts(){
        $statuts = HabilitationClient::getInstance()->getDemandeStatuts($this->getOption('filtre'));
        foreach($statuts as $key => $libelle) {
            if(HabilitationClient::getInstance()->getDemandeAutomatiqueStatut($key)) {
                $statuts[$key] .= ' ('.HabilitationClient::getInstance()->getDemandeStatutLibelle(HabilitationClient::getInstance()->getDemandeAutomatiqueStatut($key)).')';
            }
        }

        return array_merge(array("" => ""), $statuts);
    }

    public function save()
    {
        $values = $this->getValues();

        if($this->demande->date > $values['date']) {
            throw new Exception("/!\ Changement non enregistré, car il n'est pas possible de saisir un statut à une date qui est inférieure à celle du dernier statut");
        }

        $demandeKey = $this->demande->getKey();

        if(isset($values['activites']) && $values['activites'] && count($values['activites']) && count($values['activites']) < count($this->demande->getActivitesLibelle())) {

            $newDemandes = HabilitationClient::getInstance()->splitDemandeAndSave($this->getDocument()->identifiant, $demandeKey, $values['activites']);

            $demandeKey = $newDemandes[0]->getKey();
        }

        $demande = HabilitationClient::getInstance()->updateDemandeAndSave(
                                                              $this->getDocument()->identifiant,
                                                              $demandeKey,
                                                              $values['date'],
                                                              $values['statut'],
                                                              $values['commentaire'],
                                                              null,
                                                              true
                                                              );

        return $demande;
    }
    
    public function getEtablissementChais() {
        return $this->getDocument()->getEtablissementChais();
    }
}
