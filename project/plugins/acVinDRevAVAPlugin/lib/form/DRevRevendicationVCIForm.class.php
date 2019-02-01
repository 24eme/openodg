<?php
class DRevRevendicationVCIForm extends acCouchdbObjectForm
{
    protected $stock_editable = false;

    public function __construct(acCouchdbJson $object, $stock_editable = false, $options = array(), $CSRFSecret = null) {
		$this->stock_editable = $stock_editable;
        parent::__construct($object, $options, $CSRFSecret);
    }
	public function configure()
    {
        $this->embedForm('cepages', new DRevRevendicationCepagesVCIForm($this->getObject()->declaration->getProduitsVCI(), $this->stock_editable));
        $this->widgetSchema->setNameFormat('drev_vci[%s]');
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }
        $this->getObject()->calculateVolumeRevendiqueVCI();
    }
}
