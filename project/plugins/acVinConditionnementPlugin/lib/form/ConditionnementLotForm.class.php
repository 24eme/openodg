<?php
class ConditionnementLotForm extends LotForm
{

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    }

    public function configure() {
        
	parent::configure();

        if(ConditionnementConfiguration::getInstance()->hasContenances()){
          $this->setWidget('centilisation', new bsWidgetFormChoice(array('choices' => $this->getContenances())));
          $this->setValidator('centilisation', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getContenances()))));
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    protected function getContenances(){
      $contenances = ConditionnementConfiguration::getInstance()->getContenances();
      $contenances_merged = array_keys(array_merge(array("" => ""), $contenances["bouteille"], $contenances["bib"]));
      $contnenance_displaying = array_combine($contenances_merged, $contenances_merged);
      return $contnenance_displaying;
    }


}
