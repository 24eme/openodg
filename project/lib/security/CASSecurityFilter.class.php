<?php
/**
 * HTTP Authentication connected to AD filter
 * @author Tangui Morlier <tmorlier@actualys.com>
 * Inspired by James McGlinn <james@mcglinn.org>
 *
 */
class CASSecurityFilter extends sfBasicSecurityFilter
{

  protected $request = null;
  protected $routing = null;
  protected $controller = null;

  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    $this->request = $context->getRequest();
    $this->routing = $context->getRouting();
    $this->controller = $context->getController();
  }
  /**
   * Execute filter
   *
   * @param sfFilterChain $filterChain
   */
   public function execute ($filterChain)
   {
          if (!$this->context->getUser()->isAuthenticated() && ($this->request->getParameter('ticket') || isset($_SESSION["phpCAS"]["user"]))) {
           acCas::processAuth();
           if (isset($_SESSION['app_cas_origin']) && strpos($_SESSION['app_cas_origin'],'viticonnect') !== false) {
               foreach(array('cvi', 'accises', 'siret') as $type ) {
                   foreach (explode('|', acCas::getAttribute('viticonnect_entities_all_'.$type)) as $id) {
                       $e = EtablissementClient::getInstance()->findByCviOrAcciseOrPPMOrSirenOrTVA($id);
                       if ($e) {
                           break 2;
                       }
                   }
               }
               if ($e && $e->getSociete() && $e->getSociete()->getMasterCompte()) {
                   $this->getContext()->getUser()->signInOrigin($e->getSociete()->getMasterCompte()->identifiant);
               } else {
                   if (acCas::getConfig('sf_environment') == 'dev') {
                       throw new sfException('identifiant viticonnect non reconnu : '.implode(', ', acCas::getAttributes()));
                   }
                   $this->getContext()->getUser()->signInOrigin(acCas::getUser());
               }
           } else {
               $this->getContext()->getUser()->signInOrigin(acCas::getUser());
           }

           return $this->redirect($this->request->getUri());
       }

       parent::execute($filterChain);
   }

   protected function forwardToLoginAction()
   {
       $this->controller->redirect(acCas::getConfig('app_cas_url') . '/login?service=' . urlencode($this->request->getUri()));

       throw new sfStopException();
   }

}
