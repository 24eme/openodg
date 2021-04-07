<?php

class DegustationTriTableForm extends BaseForm
{
    private $tri = array();
    private $elements = array('' => '', 'Appellation' => 'Appellation', 'Genre' => 'Genre', 'Couleur' => 'Couleur', 'Cépage' => 'Cépage', 'Millesime' => 'Millesime', 'Manuel' => 'Manuel');

    public function __construct(array $tri, bool $recap = false, $options = array(), $CSRFSecret = null)
    {
        $defaults = array();
        foreach ($tri as $t) {
            $defaults['tri_'.count($defaults)] = ucFirst($t);
        }
        $defaults['recap'] = $recap;
        $this->recap = $recap;
        parent::__construct($defaults, $options, $CSRFSecret);
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

        $this->widgetSchema->setNameFormat('tritable[%s]');
    }
}
