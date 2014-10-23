<?php

class Email {
	
	private static $_instance = null;
	protected $_context;
	
	public function __construct($context = null) { 
		$this->_context = ($context)? $context : sfContext::getInstance();
	}
	
	public static function getInstance($context = null)
    {
       	if(is_null(self::$_instance)) {
       		self::$_instance = new Email($context);
		}
		return self::$_instance;
    }
    
    public function sendDrevValidation($drev) 
    {
    	if (!$drev->declarant->email) {
    		return;
    	}
        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($drev->declarant->email);
        $subject = 'Validation de votre DRev';
        $body = $this->getBodyFromPartial('send_drev_validation', array('drev' => $drev));
        $message = Swift_Message::newInstance()
  					->setFrom($from)
  					->setTo($to)
  					->setSubject($subject)
  					->setBody($body)
  					->setContentType('text/html')
  					->attach(Swift_Attachment::fromPath(sfConfig::get('sf_cache_dir').'/pdf/'.ExportDRevPDF::buildFileName($drev, true)));
		return $this->getMailer()->send($message);
    }
    
    public function sendDrevConfirmee($drev) 
    {
    	if (!$drev->declarant->email) {
    		return;
    	}
        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($drev->declarant->email);
        $subject = 'Validation de votre DRev';
        $body = $this->getBodyFromPartial('send_drev_confirmee', array('drev' => $drev));
        $message = Swift_Message::newInstance()
  					->setFrom($from)
  					->setTo($to)
  					->setSubject($subject)
  					->setBody($body)
  					->setContentType('text/html');
		return $this->getMailer()->send($message);
    }

    protected function getMailer() 
    {
        return $this->_context->getMailer();
    }

    protected function getBodyFromPartial($partial, $vars = null) 
    {
        return $this->_context->getController()->getAction('Email', 'main')->getPartial('Email/' . $partial, $vars);
    }
}