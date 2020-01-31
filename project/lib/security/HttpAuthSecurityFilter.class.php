<?php

class HttpAuthSecurityFilter extends sfBasicSecurityFilter
{

  /**
   * Execute filter
   *
   * @param sfFilterChain $filterChain
   */
   public function execute($filterChain)
   {
        if ($this->getContext()->getUser()->isAuthenticated()){
           return parent::execute($filterChain);
        }

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
           http_response_code(401);
           echo "Unauthorized";
           exit(0);
        }

        $this->getContext()->getUser()->signInOrigin($_SERVER['PHP_AUTH_USER']);

        parent::execute($filterChain);
   }
}
