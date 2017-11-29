<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
    protected static $routing = null;

    public function setup()
    {
        $this->enablePlugins('acCouchdbPlugin');
        $this->enablePlugins('acVinLibPlugin');
        $this->enablePlugins('acTCPDFPlugin');
        $this->enablePlugins('acCASPlugin');
        $this->enablePlugins('DeclarationPlugin');
        $this->enablePlugins('FichierPlugin');
        $this->enablePlugins('acVinGenerationPlugin');
        $this->enablePlugins('acVinDocumentPlugin');
        $this->enablePlugins('acLdapPlugin');
        $this->enablePlugins('EmailPlugin');
        $this->enablePlugins('acExceptionNotifierPlugin');
        $this->enablePlugins('acElasticaPlugin');
        $this->enablePlugins('acVinFacturePlugin');
        $this->enablePlugins('AppPlugin');

        // Spécifique Rhône
        $this->enablePlugins('acVinDRevPlugin');
        $this->enablePlugins('acVinConfigurationPlugin');
        $this->enablePlugins('acVinHabilitationPlugin');
        $this->enablePlugins('acVinComptePlugin');
        $this->enablePlugins('acVinSocietePlugin');
        $this->enablePlugins('acVinEtablissementPlugin');
        $this->enablePlugins('DRPlugin');
        $this->enablePlugins('SV11Plugin');
        $this->enablePlugins('SV12Plugin');

        // Spécifique AVA
        /*$this->enablePlugins('CompteAVAPlugin');
        $this->enablePlugins('EtablissementAVAPlugin');
        $this->enablePlugins('acVinDRevAVAPlugin');
        $this->enablePlugins('acVinConfigurationAVAPlugin');
        $this->enablePlugins('acVinAbonnementPlugin');
        $this->enablePlugins('acVinTiragePlugin');
        $this->enablePlugins('acVinDRevMarcPlugin');
        $this->enablePlugins('acVinTravauxMarcPlugin');
        $this->enablePlugins('acVinDegustationPlugin');
        $this->enablePlugins('acVinParcellairePlugin');
        $this->enablePlugins('acVinTourneePlugin');
        $this->enablePlugins('acVinConstatsVTSGNPlugin');*/
    }

    public static function getAppRouting()
    {
        if (null !== self::$routing) {
            return self::$routing;
        }
        if (sfContext::hasInstance() && sfContext::getInstance()->getRouting()) {
            self::$routing = sfContext::getInstance()->getRouting();
        } else {
            if (!self::hasActive()) {
                throw new sfException('No sfApplicationConfiguration loaded');
            }
            $appConfig = self::getActive();
            $config = sfFactoryConfigHandler::getConfiguration($appConfig->getConfigPaths('config/factories.yml'));
            $params = array_merge($config['routing']['param'], array('load_configuration' => false,
                                                                     'logging'            => false,
                                                                     'context'            => array('host'      => sfConfig::get('app_routing_context_production_host', 'localhost'),
                                                                                                   'prefix'    => sfConfig::get('app_prefix', sfConfig::get('sf_no_script_name') ? '' : '/'.$appConfig->getApplication().'_'.$appConfig->getEnvironment().'.php'),
                                                                                                   'is_secure' => sfConfig::get('app_routing_context_secure', false))));
            $handler = new sfRoutingConfigHandler();
            $routes = $handler->evaluate($appConfig->getConfigPaths('config/routing.yml'));
            $routeClass = $config['routing']['class'];
            self::$routing = new $routeClass($appConfig->getEventDispatcher(), null, $params);
            self::$routing->setRoutes($routes);
        }
        return self::$routing;
    }
}
