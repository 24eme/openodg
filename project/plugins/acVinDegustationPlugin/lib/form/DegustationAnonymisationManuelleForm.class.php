<?php

class DegustationAnonymisationManuelleForm extends acCouchdbObjectForm
{
    private $tableLots = null;
    private $numero_table = null;

    public function __construct(acCouchdbJson $degustation, $options = array(), $CSRFSecret = null)
    {
        parent::__construct($degustation, $options, $CSRFSecret);
    }

    public function configure()
    {
        foreach ($this->getObject()->getLotsDegustables() as $lot) {
            $name = $this->getWidgetNameFromLot($lot);
            $this->setWidget($name , new sfWidgetFormInput([], ['required' => false]));
            $this->setValidator($name, new sfValidatorString(['required' => false]));
        }

        $this->widgetSchema->setNameFormat('tables[%s]');

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(['callback' => [$this, 'checkUnicity']])
        );
    }

    public function checkUnicity($validator, $values)
    {
        $values = array_filter($values);
        $duplicates = array_diff_assoc($values, array_unique($values));
        if (count($duplicates)) {
            throw new sfValidatorError($validator, sprintf("Des valeurs ont le même numéro d'anonymat : %s", implode(", ", array_values($duplicates))));
        }

        return $values;
    }

    public function getWidgetNameFromLot($lot)
    {
        if ($lot->isLeurre()) {
            return 'lot_leure-'.$lot->getKey();
        }
        return 'lot_'.$lot->declarant_identifiant."-".$lot->unique_id;
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        foreach ($this->getObject()->lots as $lot) {
            $name = $this->getWidgetNameFromLot($lot);
            if (isset($values[$name]) && $values[$name]) {
                $lot->numero_anonymat = $values[$name];
            } else {
                $lot->numero_anonymat = null;
            }
        }
    }

    protected function updateDefaultsFromObject()
    {
        $defaults = $this->getDefaults();
        foreach ($this->getObject()->lots as $lot) {
            if ($lot->exist('numero_anonymat')) {
                $defaults[$this->getWidgetNameFromLot($lot)] = $lot->numero_anonymat;
            }
        }
        $this->setDefaults($defaults);
    }

    protected function doSave($con = null)
    {
        $this->updateObject();
        $this->object->getCouchdbDocument()->save(false);
    }
}
