<?php
class DRevLotForm extends LotForm
{
    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        if (DRevConfiguration::getInstance()->hasSpecificiteLot()) {
            $options['specificites'] = DRevConfiguration::getInstance()->getSpecificites();
        }
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    }
}
