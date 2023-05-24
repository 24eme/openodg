<?php

class DegustationOrganisationManuelleForm extends acCouchdbObjectForm
{
    private $tableLots = null;
    private $numero_table = null;

    public function __construct(acCouchdbJson $degustation, $numero_table, $options = array(), $CSRFSecret = null)
    {
        $this->numero_table = $numero_table;
        $this->tableLots = $degustation->getLotsTableOrFreeLots($this->numero_table);
        parent::__construct($degustation, $options, $CSRFSecret);
    }

    public function configure()
    {
        foreach ($this->tableLots as $lot) {
            $name = $this->getWidgetNameFromLot($lot);
            $this->setWidget($name , new sfWidgetFormInput([], ['required' => false]));
            $this->setValidator($name, new sfValidatorString(['required' => false]));
        }

        $this->widgetSchema->setNameFormat('tables[%s]');
    }

    public function getTableLots()
    {
        return $this->tableLots;
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
        foreach ($this->tableLots as $lot) {
            $name = $this->getWidgetNameFromLot($lot);
            if ($values[$name]) {
                $lot->numero_table = $this->numero_table;
                $lot->numero_anonymat = $values[$name];
            }
        }
    }

    protected function updateDefaultsFromObject()
    {
        $defaults = $this->getDefaults();
        foreach ($this->getObject()->lots as $lot) {
            if ($lot->exist('numero_table') && $lot->exist('numero_anonymat') && ($lot->numero_table == $this->numero_table)) {
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
