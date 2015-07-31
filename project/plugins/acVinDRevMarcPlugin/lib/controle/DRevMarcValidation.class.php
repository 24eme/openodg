<?php
class DRevMarcValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
	
	public function __construct($document, $options = null)
    {
        parent::__construct($document, $options);
        $this->noticeVigilance = false;
    }
    
  	public function configure() 
  	{
    	
  	}

    public function controle()
    {
    }
  	
  

}