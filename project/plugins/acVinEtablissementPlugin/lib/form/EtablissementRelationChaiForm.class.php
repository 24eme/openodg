<?php

class EtablissementRelationChaiForm extends acCouchdbForm {
    protected $etablissementRelation = null;
    protected $typeLiaison = null;

    public function __construct(acCouchdbDocument $doc, $typeLiaison, $etablissementRelation, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->etablissementRelation = $etablissementRelation;
        $this->typeLiaison = $typeLiaison;
      parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function configure() {
        parent::configure();

        $this->setWidget('hash_chai', new sfWidgetFormChoice(array('expanded' => false, 'multiple' => false, 'choices' => $this->getChais())));
        $this->widgetSchema->setLabel('hash_chai', 'Chai :');
        $this->setValidator('hash_chai', new sfValidatorChoice(array('required' => false, 'multiple' => false, 'choices' => array_keys($this->getChais()))));

        $this->setWidget('attributs_chai', new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $this->getAttributs())));
        $this->widgetSchema->setLabel('attributs_chai', 'Attributs :');
        $this->setValidator('attributs_chai', new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($this->getAttributs()))));

        $this->widgetSchema->setNameFormat('etablissement_relation[%s]');
    }

    public function save() {
        $liaison = $this->getDocument()->addLiaison($this->typeLiaison, $this->etablissementRelation, true, $this->etablissementRelation->get($this->getValue('hash_chai')), $this->getValue('attributs_chai'));
        $this->getDocument()->save();

        return $liaison;
    }

    public function getChais() {
        $chais = array();
        if($this->etablissementRelation->exist('chais')) {
            foreach($this->etablissementRelation->chais as $chai) {
                $chais[$chai->getHash()] = $chai->nom." - ".$chai->adresse.", ".$chai->code_postal." ".$chai->commune;
            }
        }
        return array_merge(array("" => ""), $chais);
    }

    public function getAttributs(){
      return EtablissementClient::$chaisAttributsLibelles;
    }

}
