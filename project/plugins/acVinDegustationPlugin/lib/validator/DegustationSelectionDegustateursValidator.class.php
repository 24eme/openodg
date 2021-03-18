<?php

class DegustationSelectionDegustateursValidator extends sfValidatorSchema {

    protected  $_object;
    protected  $college;

    public function __construct($object, $fields = null, $options = array(), $messages = array())
    {
        $this->_object = $object;
        $this->college = $options['college'];
        parent::__construct($fields, array(), $messages);
    }

    public function configure($options = array(), $messages = array()) {
        $this->addMessage('error_degustateur', 'Vous avez déjà choisi le dégustateur "%degustateur%" en tant que : %college%');
    }


    protected function doClean($values) {

        $degustateurs = $this->_object->getDegustateurs();

        $colleges = DegustationConfiguration::getInstance()->getColleges();
        unset($colleges[$this->college]);

        foreach ($values as $collegesForm) {
            if(!is_array($collegesForm)){
                continue;
            }
            foreach ($collegesForm[$this->college] as $id => $value) {
                if($value["selectionne"]){
                    foreach ($colleges as $collegeKey => $college) {
                        if($degustateurs->exist($collegeKey) && $degustateurs->get($collegeKey)->exist($id)){
                            $c = CompteClient::getInstance()->find($id);
                            throw new sfValidatorErrorSchema($this, array(new sfValidatorError($this, 'error_degustateur',array("degustateur" => $c->nom_a_afficher, "college" => $college))));
                        }
                    }
                }
            }
        }
        return $values;
    }

}
