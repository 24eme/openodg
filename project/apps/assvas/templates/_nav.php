<?php $route = $route->getRawValue(); ?>
<nav id="menu_navigation" class="navbar navbar-default container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed hidden-lg hidden-md" data-toggle="collapse" data-target="#menu_items_collapse" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" style="padding-top: 0;padding-right: 15px;" href="<?php echo url_for('accueil') ?>"><h1 style="color: black; font-size: 1.5em; margin-top: 10px; font-weight: bold;">ASSVAS</h1></a>
        </div>

        <div class="collapse navbar-collapse" id="menu_items_collapse" style="">
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
                <ul class="nav navbar-nav <?php if($compte): ?>mode-operateur<?php endif; ?>" style="border: 0;">
                    <li class="<?php if($route instanceof InterfaceParcellaireRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceParcellaireRoute): ?><?php echo url_for('parcellaire_declarant', $etablissement); ?><?php else: ?><?php echo url_for('parcellaire'); ?><?php endif; ?>">Parcellaire</a></li>
                    <li class="<?php if($route instanceof InterfaceDeclarationRoute): ?>active<?php endif; ?>  <?php if(!$sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>disabled<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDeclarationRoute): ?><?php echo url_for('declaration_etablissement', $etablissement); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">DR / SV</a></li>
                    <li class="<?php if($route instanceof InterfaceDocumentsRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDocumentsRoute): ?><?php echo url_for('pieces_historique', $etablissement); ?><?php else: ?><?php echo url_for('documents'); ?><?php endif; ?>">Documents</a></li>
                    <li class="<?php if($route instanceof InterfaceHabilitationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceHabilitationRoute): ?><?php echo url_for('habilitation_declarant', $etablissement); ?><?php else: ?><?php echo url_for('habilitation'); ?><?php endif; ?>">Habilitations</a></li>
                    <li class="<?php if($route instanceof InterfaceCompteRoute && !$route instanceof FacturationDeclarantRoute): ?>active<?php endif; ?>"><a href="<?php if($compte && !$route instanceof InterfaceCompteRoute || $route instanceof FacturationDeclarantRoute): ?><?php echo url_for('compte_visualisation', $compte); ?><?php else: ?><?php echo url_for('compte_search'); ?><?php endif; ?>">Contacts</a></li>
                </ul>
                <?php elseif($sf_user->isAuthenticated() && $etablissement): ?>
                    <ul class="nav navbar-nav <?php if($compte): ?>mode-operateur<?php endif; ?>" style="border: 0;">
                        <li class="<?php if($route instanceof InterfaceParcellaireRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire</a></li>
                        <li class="<?php if($route instanceof InterfaceDocumentsRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('pieces_historique', $etablissement); ?>">Documents</a></li>
                        <li class="<?php if(preg_match('/compte/', $route->getParameters()['module'])): ?>active<?php endif; ?>"><a tabindex="-1" href="<?php echo url_for("compte_teledeclarant_modification") ?>" title="Mon compte">Mon compte</a></li>
                    </ul>
            <?php endif; ?>
            <ul class="nav navbar-nav navbar-right">
                <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
                    <?php if(sfConfig::get('app_nav_stats_'.sfConfig::get('sf_app'))): ?>
                    <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-stats"></span><span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        <?php foreach(sfConfig::get('app_nav_stats_'.sfConfig::get('sf_app')) as $i => $navItem):
                                $campagne = ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent();
                                if($i > 0 && isset($navItem['title'])): ?><li role="separator" class="divider"></li><?php endif; ?>
                         <li>
                             <?php if (isset($navItem['etablissement']) && $etablissement): ?>
                                 <a href="<?php echo str_replace('CAMPAGNE', $campagne, sprintf($navItem['url'].'?op%%25C3%%25A9rateur=%s', $etablissement->raison_sociale)) ?>">
                             <?php else: ?>
                                 <a href="<?php echo str_replace('CAMPAGNE', $campagne, $navItem['url']) ?>">
                             <?php endif ?>
                                 <?php if(isset($navItem['icon'])): ?><span class="glyphicon glyphicon-<?php echo $navItem['icon'] ?>"></span><?php endif; ?>
                                 <?php if(isset($navItem['title'])): ?><strong><?php endif; ?><?php echo $navItem['name'] ?><?php if(isset($navItem['title'])): ?></strong><?php endif; ?>
                             </a>
                        </li>
                        <?php endforeach; ?>
                      </ul>
                    </li>
                <?php endif; ?>
                <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog"></span><span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li><a href="<?php echo url_for("produits") ?>">Configuration</a></li>
                    <li><a href="<?php echo url_for("generation_list") ?>">Tâches récurrentes</a></li>
                  </ul>
                </li>
                <?php elseif($sf_user->isAuthenticated()): ?>
                 <li><a tabindex="-1" href="<?php echo url_for("compte_teledeclarant_modification") ?>" title="Mon compte"><span class="glyphicon glyphicon-user"></span></a></li>
                <?php endif; ?>
                <?php if ($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN) && $compte && $route instanceof InterfaceUsurpationRoute && !$sf_user->isUsurpationCompte()) : ?>
                     <li><a tabindex="-1" href="<?php echo url_for('auth_usurpation', array('identifiant' => $compte->identifiant)) ?>" title="Connexion mode déclarant"><span class="glyphicon glyphicon-cloud-upload"></span></a></li>
                <?php endif; ?>
                <?php if ($sf_user->isUsurpationCompte()): ?>
                    <li><a tabindex="-1" href="<?php echo url_for('auth_deconnexion_usurpation') ?>" title="Déconnexion du mode déclarant"><span class="glyphicon glyphicon-cloud-download"></span></a></li>
                <?php elseif ($sf_user->isAuthenticated()): ?>
                    <li><a tabindex="-1" href="<?php echo url_for('auth_logout') ?>" title="Déconnexion"><span class="glyphicon glyphicon-log-out"></span></a></li>
                <?php else: ?>
                    <li><a tabindex="-1" href="<?php echo url_for('common_accueil') ?>">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
</nav>
