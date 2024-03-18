<?php

class DegustationSelectionOperateursForm extends acCouchdbObjectForm
{
    private $operateurs = [];
    private $etablissement_idenfiant = null;
    public function __construct(acCouchdbJson $object, $identifiant = null, $options = array(), $CSRFSecret = null) {
        $this->etablissement_idenfiant = $identifiant;
        parent::__construct($object, $options = array(), $CSRFSecret = null);
    }

    public function configure()
    {

        $identifiant_options = ['class' => 'form-control select2autocompleteAjax', 'required' => true, 'readonly' => boolval($this->etablissement_idenfiant)];
        $this->setWidget('identifiant', new WidgetEtablissement(['interpro_id' => 'INTERPRO-declaration'], $identifiant_options));

        $this->widgetSchema->setLabel('identifiant', 'Établissement');
        $this->setValidator('identifiant', new ValidatorEtablissement(array('required' => true)));
        $this->validatorSchema['identifiant']->setMessage('required', 'Le choix d\'un etablissement est obligatoire');

        $this->setWidget('initial_type', new bsWidgetFormChoice(['choices' => TourneeClient::$lotTourneeChoices], ['required' => true, 'readonly' => boolval($this->etablissement_idenfiant)]));
        $this->widgetSchema->setLabel('initial_type', 'Type de contrôle');
        $this->setValidator('initial_type', new sfValidatorChoice(['choices' => array_keys(TourneeClient::$lotTourneeChoices)]));
        $this->validatorSchema['initial_type']->setMessage('required', 'Le choix d\'un type est obligatoire');

        $this->widgetSchema['details'] = new bsWidgetFormInput([], ['list' => 'liste-appellations', 'required' => true, 'readonly' => boolval($this->etablissement_idenfiant)]);
        $this->widgetSchema['details']->setLabel("Appellation à controler");
        $this->validatorSchema['details'] = new sfValidatorString(array('required' => false));

        $this->setWidget('liste-appellations', new sfWidgetDatalist(['choices' => $this->getListeAppellations()]));

        $this->setWidget('chai', new bsWidgetFormChoice(['choices' => $this->getChais()], ['readonly' => !boolval($this->etablissement_idenfiant), 'required' => boolval($this->etablissement_idenfiant)]));
        $this->widgetSchema->setLabel('chai', 'Chai');
        $this->setValidator('chai', new sfValidatorChoice(array('choices' => array_keys($this->getChais()))));

        $this->widgetSchema->setNameFormat('selection_operateur[%s]');
    }

    public function getChais() {
        if(!$this->etablissement_idenfiant) {
            return [];
        }
        $etablissement = EtablissementClient::getInstance()->find($this->etablissement_idenfiant);
        $chais = $etablissement->getAllChais();
        if (count($chais) > 0) {
            $chais = [null => null] + $chais;
        }

        return $chais;
    }

    protected function doUpdateObject($values)
    {
        $etablissement = EtablissementClient::getInstance()->find($values['identifiant']);
        $chai = $etablissement->chais->get($values['chai']);
        $lot = $this->getObject()->add('lots')->add();
        $lot->date = date('Y-m-d');
        $lot->id_document = $this->getObject()->_id;
        $lot->declarant_identifiant = $etablissement->getIdentifiant();
        $lot->declarant_nom = $etablissement->getNom();
        $lot->adresse_logement = sprintf("%s — %s — %s %s", $chai->nom, $chai->adresse, $chai->code_postal, $chai->commune);
        $lot->affectable = false;
        $lot->initial_type = $values['initial_type'];
        $lot->details = $values['details'];
    }

    private function getListeAppellations()
    {
        return array_unique(array_map(function ($p) {
                return $p->getAppellation()->getLibelle();
            }, $this->getObject()->getConfiguration()->getProduits())
        );
    }
}
