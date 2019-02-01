<?php
class DRevRevendicationCepagesVCIForm extends sfForm
{
    protected $produits;
    protected $stock_editable = false;

	public function __construct($cepages, $stock_editable = false, $defaults = array(), $options = array(), $CSRFSecret = null)
  	{
        $this->cepages = $cepages;
		$this->stock_editable = $stock_editable;
        parent::__construct($defaults, $options, $CSRFSecret);
    }

   	public function configure()
    {
        foreach ($this->cepages as $hash => $cepage) {
            $form = new DRevRevendicationCepageVCIForm($cepage, $this->stock_editable);
            $this->embedForm($hash, $form);
        }

    }

    public function doUpdateObject($values)
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            unset($values[$key]['_revision']);
        	$embedForm->doUpdateObject($values[$key]);
        }
    }


}
