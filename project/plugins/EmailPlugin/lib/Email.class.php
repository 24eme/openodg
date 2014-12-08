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
    
    public function sendDRevValidation($drev) 
    {
      	if (!$drev->declarant->email) {
      		  
            return;
      	}
        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($drev->declarant->email);
        $subject = 'Validation de votre Déclaration de Revendication';
        $body = $this->getBodyFromPartial('send_drev_validation', array('drev' => $drev));
        $message = Swift_Message::newInstance()
  					->setFrom($from)
  					->setTo($to)
  					->setSubject($subject)
  					->setBody($body)
  					->setContentType('text/plain');

		    return $this->getMailer()->send($message);
    }
    
    public function sendDRevConfirmee($drev) 
    {
      	if (!$drev->declarant->email) {
      		  
            return;
      	}

        $pdf = new ExportDRevPdf($drev);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($drev->declarant->email);
        $subject = 'Validation définitive de votre Déclaration de Revendication';
        $body = $this->getBodyFromPartial('send_drev_confirmee', array('drev' => $drev));
        $message = Swift_Message::newInstance()
  					->setFrom($from)
  					->setTo($to)
  					->setSubject($subject)
  					->setBody($body)
  					->setContentType('text/plain')
            ->attach($pdfAttachment);

		    return $this->getMailer()->send($message);
    }

    public function sendDRevRappelDocuments($drev) 
    {
        if (!$drev->declarant->email) {
          
            return;
        }

        if($drev->hasCompleteDocuments()) {

            return;
        }

        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($drev->declarant->email);
        $subject = "Rappel - Documents à envoyer pour votre déclaration de Revendication";
        $body = $this->getBodyFromPartial('send_drev_rappel_documents', array('drev' => $drev));
        $message = Swift_Message::newInstance()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType('text/plain');

        return $this->getMailer()->send($message);
    }
    
    public function sendDRevMarcValidation($drevmarc) 
    {
      	if (!$drevmarc->declarant->email) {
      		
            return;
      	}

        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($drevmarc->declarant->email);
        $subject = "Validation de votre Déclaration de Revendication Marc d'Alsace Gewurztraminer";
        $body = $this->getBodyFromPartial('send_drevmarc_validation', array('drevmarc' => $drevmarc));
        $message = Swift_Message::newInstance()
  					->setFrom($from)
  					->setTo($to)
  					->setSubject($subject)
  					->setBody($body)
  					->setContentType('text/plain');

		    return $this->getMailer()->send($message);
    }
    
    public function sendDRevMarcConfirmee($drevmarc) 
    {
      	if (!$drevmarc->declarant->email) {
      		
            return;
      	}

        $pdf = new ExportDRevMarcPDF($drevmarc);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->generate();
        $pdfAttachment = new Swift_Attachment($pdf->output(), $pdf->getFileName(), 'application/pdf');

        $from = array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name'));
        $to = array($drevmarc->declarant->email);
        $subject = "Validation définitive de votre Déclaration de Revendication Marc d'Alsace Gewurztraminer";
        $body = $this->getBodyFromPartial('send_drevmarc_confirmee', array('drevmarc' => $drevmarc));
        $message = Swift_Message::newInstance()
  					->setFrom($from)
  					->setTo($to)
  					->setSubject($subject)
  					->setBody($body)
  					->setContentType('text/plain')
            ->attach($pdfAttachment);

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

    public function getPartial($templateName, $vars = null)
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

      $vars = null !== $vars ? $vars : $this->varHolder->getAll();

      return get_partial($templateName, $vars);
    }
}