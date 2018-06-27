<?php
class HabilitationDemandeDonneesIdentificationForm extends BaseForm
{
    protected $doc = null;

    public function getDocument() {

        return $this->doc;
    }

    public function __construct($doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->doc = $doc;

        $defaults['raison_sociale'] = $doc->declarant->raison_sociale;
        $defaults['cvi'] = $doc->declarant->cvi;
        $defaults['siret'] = $doc->declarant->siret;
        $defaults['adresse'] = $doc->declarant->adresse;
        $defaults['code_postal'] = $doc->declarant->code_postal;
        $defaults['commune'] = $doc->declarant->commune;

        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure()
    {
        $this->setWidget('raison_sociale', new sfWidgetFormInput(array(), array()));
        $this->setValidator('raison_sociale', new sfValidatorString(array("required" => false)));

        $this->setWidget('cvi', new sfWidgetFormInput(array(), array()));
        $this->setValidator('cvi', new sfValidatorString(array("required" => false)));

        $this->setWidget('siret', new sfWidgetFormInput(array(), array()));
        $this->setValidator('siret', new sfValidatorString(array("required" => false)));

        $this->setWidget('adresse', new sfWidgetFormInput(array(), array()));
        $this->setValidator('adresse', new sfValidatorString(array("required" => false)));

        $this->setWidget('code_postal', new sfWidgetFormInput(array(), array()));
        $this->setValidator('code_postal', new sfValidatorString(array("required" => false)));

        $this->setWidget('commune', new sfWidgetFormInput(array(), array()));
        $this->setValidator('commune', new sfValidatorString(array("required" => false)));
    }

}
