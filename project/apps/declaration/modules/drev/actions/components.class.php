<?php
class drevComponents extends sfComponents 
{
    
    public function executeMonEspace(sfWebRequest $request) 
    {
      $this->drev = DRevClient::getInstance()->find('DREV-7523700100-2013-2014');
    }
    
}
