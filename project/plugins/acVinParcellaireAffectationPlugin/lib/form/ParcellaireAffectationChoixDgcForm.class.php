<?php

class ParcellaireAffectationChoixDgcForm extends acCouchdbObjectForm {
    
    protected $configuration;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->configuration = ConfigurationClient::getCurrent();
        parent::__construct($object, $options, $CSRFSecret);
    }
    
    public function configure() {
    	if($this->getObject()->isPapier()) {
    		$this->setWidget('date_papier', new sfWidgetFormInput());
    		$this->setValidator('date_papier', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
    		$this->getWidget('date_papier')->setLabel("Date de réception du document");
    		$this->getValidator('date_papier')->setMessage("required", "La date de réception du document est requise");
    	}
    	$dgcChoices = $this->getDgcChoices();
   		$this->setWidget('dgc', new sfWidgetFormChoice(array('multiple' => true, 'expanded' => true,'choices' => $dgcChoices, 'renderer_options' => array('formatter' => array($this, 'formatter')))));
   		$this->setValidator('dgc', new sfValidatorChoice(array('multiple' => true, 'required' => true, 'choices' => array_keys($dgcChoices)),array('required' => "Vous devez selectionner vos dénomination complémentaire")));
    	$this->getWidget('dgc')->setLabel("Dénomination complémentaire : ");
    	$this->getWidget('dgc')->setAttributes(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success"));
        $this->widgetSchema->setNameFormat('choix_dgc[%s]');
    }
    
    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $defaults = $this->getDefaults();
        $obj = $this->getObject();
        if ($dgc = $obj->getDgc()) {
            $defaults['dgc'] = array_keys($dgc);
        }
        $this->setDefaults($defaults);
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        if (isset($values['dgc']) && count($values['dgc']) > 0) {
            $this->getObject()->addParcellesFromParcellaire($values['dgc']);
        }
    }
    
    private function getDgcChoices() {
        $lieux = $this->configuration->getLieux();
        ksort($lieux);
        return $lieux;
    }

    public function formatter($widget, $inputs) {
        $rows = array();
        $denominations = array_flip($this->configuration->getLieux());
        foreach ($inputs as $input) {
            $libelle = strip_tags($input['label']);
            $code = (isset($denominations[$libelle]))? $denominations[$libelle] : null;
            $existChoice = ($code)? $this->getObject()->existDgcFromParcellaire($code) : false; 
            if ($existChoice) {
                $rows[] = '<tr><td>'.strip_tags($input['label']).'</td><td>'.$input['input'].'</td></tr>';
            } else {
                $rows[] = '<tr><td><span class="text-muted">'.strip_tags($input['label']).'</span></td><td>'.preg_replace('/>$/', ' disabled="disabled">', $input['input']).'</td></tr>';
            }
        }

        return!$rows ? '' : implode($widget->getOption('separator'), $rows);
    }

}
