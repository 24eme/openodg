<?php

class DegustationTriTableForm extends BaseForm
{
    private $tri = array();
    private $elements = array('' => '',
        DegustationClient::DEGUSTATION_TRI_APPELLATION => 'Appellation',
        DegustationClient::DEGUSTATION_TRI_GENRE => 'Genre',
        DegustationClient::DEGUSTATION_TRI_COULEUR => 'Couleur',
        DegustationClient::DEGUSTATION_TRI_CEPAGE => 'Cépage',
        DegustationClient::DEGUSTATION_TRI_MILLESIME => 'Millesime',
        DegustationClient::DEGUSTATION_TRI_MANUEL => 'Manuel'
    );

    public function __construct(array $tri, bool $recap = false, $options = array(), $CSRFSecret = null)
    {
        $this->defaults = array();
        foreach ($tri as $t) {
            $this->defaults['tri_'.count($this->defaults)] = $t;
        }
        $this->defaults['recap'] = $recap;
        $this->recap = $recap;
        parent::__construct($this->defaults, $options, $CSRFSecret);
    }

    public function configure()
    {
        for($i = 0 ; $i < count($this->elements) -1 ; $i++) {
            $this->setWidget('tri_'.$i, new sfWidgetFormChoice(array('choices' => $this->elements)));
            $this->setValidator('tri_'.$i, new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->elements))));
            $this->widgetSchema->setLabel('tri_'.$i, 'Tri '.($i + 1).' : ');
        }

        $this->setWidget('recap', new sfWidgetFormInputHidden());
        $this->setValidator('recap', new sfValidatorPass());
        foreach($this->defaults as $id => $d) {
            $this->widgetSchema[$id]->setDefault($d);
        }
        $this->widgetSchema->setNameFormat('tritable[%s]');
    }

}
