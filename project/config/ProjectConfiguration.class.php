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
        $this->enablePlugins('acVinHabilitationPlugin');
        $this->enablePlugins('MandatSepaPlugin');

        if(getenv("APPLICATION") == "ava") {
            $this->enablePlugins('CompteAVAPlugin');
            $this->enablePlugins('EtablissementAVAPlugin');
            $this->enablePlugins('acVinDRevAVAPlugin');
            $this->enablePlugins('acVinConfigurationAVAPlugin');
            $this->enablePlugins('acVinAbonnementPlugin');
            $this->enablePlugins('acVinTiragePlugin');
            $this->enablePlugins('acVinDRevMarcPlugin');
            $this->enablePlugins('acVinTravauxMarcPlugin');
            $this->enablePlugins('acVinDegustationAVAPlugin');
            $this->enablePlugins('acVinTourneePlugin');
            $this->enablePlugins('acVinConstatsVTSGNPlugin');
            $this->enablePlugins('acVinRegistreVCIPlugin');
            $this->enablePlugins('acVinParcellaireAffectationAVAPlugin');
            return;
        }

        $this->enablePlugins('AppPlugin');
        $this->enablePlugins('acVinParcellairePlugin');
        $this->enablePlugins('acVinParcellaireIrrigablePlugin');
        $this->enablePlugins('acVinParcellaireIrriguePlugin');
        $this->enablePlugins('acVinParcellaireAffectationPlugin');
        $this->enablePlugins('acVinDRevPlugin');
        $this->enablePlugins('acVinConfigurationPlugin');
        $this->enablePlugins('acVinComptePlugin');
        $this->enablePlugins('acVinSocietePlugin');
        $this->enablePlugins('acVinEtablissementPlugin');
        $this->enablePlugins('DRPlugin');
        $this->enablePlugins('SV11Plugin');
        $this->enablePlugins('SV12Plugin');
        $this->enablePlugins('acVinPotentielProductionPlugin');
        $this->enablePlugins('acVinDegustationPlugin');
        $this->enablePlugins('acVinChgtDenomPlugin');
        $this->enablePlugins('acVinConditionnementPlugin');
        $this->enablePlugins('acVinTransactionPlugin');
    }

    public function setRootDir($rootDir)
    {
        parent::setRootDir($rootDir);

        if(getenv("APPLICATION") == "ava") {
            sfConfig::set('sf_test_dir', sfConfig::get('sf_root_dir')."/test_ava");
        }
    }

    public function setCacheDir($cacheDir)
    {
        if(getenv("APPLICATION") == "ava") {
            sfConfig::set('sf_cache_dir', $cacheDir.DIRECTORY_SEPARATOR."ava");
        } else {
            parent::setCacheDir($cacheDir);
        }
    }

    public static function getAppRouting()
    {
        if (null !== self::$routing) {
            return self::$routing;
        }

        if (!self::hasActive()) {
            throw new sfException('No sfApplicationConfiguration loaded');
        }
        $appConfig = self::getActive();
        $config = sfFactoryConfigHandler::getConfiguration($appConfig->getConfigPaths('config/factories.yml'));
        $params = array_merge($config['routing']['param'], array('load_configuration' => false,
                                                                 'logging'            => false,
                                                                 'context'            => array('host'      => sfConfig::get('app_routing_context_production_host', 'localhost'),
                                                                                               'prefix'    => sfConfig::get('app_prefix', sfConfig::get('sf_no_script_name') ? '' : '/'.$appConfig->getApplication().'_'.$appConfig->getEnvironment().'.php'),
                                                                                               'is_secure' => sfConfig::get('app_routing_context_secure', true))));
        $handler = new sfRoutingConfigHandler();
        $routes = $handler->evaluate($appConfig->getConfigPaths('config/routing.yml'));
        $routeClass = $config['routing']['class'];
        self::$routing = new $routeClass($appConfig->getEventDispatcher(), null, $params);
        self::$routing->setRoutes($routes);

        return self::$routing;
    }
}
