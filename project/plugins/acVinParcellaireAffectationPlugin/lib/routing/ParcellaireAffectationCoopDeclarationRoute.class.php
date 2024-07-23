<?php
abstract class ParcellaireAffectationCoopDeclarationRoute extends EtablissementRoute {

    public function generate($params, $context = array(), $absolute = false)
    {
        if(sfContext::getInstance()->getRequest()->getParameter('coop')) {
            $params['coop'] = sfContext::getInstance()->getRequest()->getParameter('coop');
        }
        return parent::generate($params, $context, $absolute);
    }

}
