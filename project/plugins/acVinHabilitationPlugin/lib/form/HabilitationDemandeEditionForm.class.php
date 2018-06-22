<?php
class HabilitationDemandeEditionForm extends acCouchdbForm
{
    protected $demande = null;

    public function __construct($doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        if($doc instanceof HabilitationDemande) {
            $this->demande = $doc;
            $doc = $doc->getDocument();
            //$defaults['statut'] = $this->demande->statut;
            //$defaults['date'] = date('d/m/Y');
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
                'required' => true)),
            'statut' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($statuts))),
            'commentaire' => new sfValidatorString(array("required" => false)),
        ));

        $this->widgetSchema->setNameFormat('habilitation_demande_edition[%s]');
    }

    public function getStatuts(){

        return array_merge(array("" => ""), HabilitationClient::$demande_statut_libelles);
    }

    public function save()
    {
        $values = $this->getValues();

        $demande = HabilitationClient::getInstance()->updateDemandeAndSave(
                                                              $this->getDocument()->identifiant,
                                                              $this->demande->getKey(),
                                                              $values['date'],
                                                              $values['statut'],
                                                              $values['commentaire'],
                                                              ""
                                                              );

        return $demande;
    }
}
