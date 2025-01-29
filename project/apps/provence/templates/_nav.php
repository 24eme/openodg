<?php
$route = ($sf_request->getAttribute('sf_route')) ? $sf_request->getAttribute('sf_route')->getRawValue() : NULL;
$etablissement = null;
$compte = null;

if ($route instanceof EtablissementRoute) {
    $etablissement = $route->getEtablissement();
    $compte = $etablissement->getMasterCompte();
}
if ($route instanceof FacturationDeclarantRoute || $route instanceof FactureRoute || $route instanceof CompteRoute) {
    $compte = $route->getCompte();
    $etablissement = $compte->getEtablissement();
}
if ($route instanceof SocieteRoute) {
    $etablissement = $route->getEtablissement();
    $societe = $route->getSociete();
    if ($societe) {
        $compte = $societe->getMasterCompte();
    }
}
if ($sf_user->isAuthenticated() && !$sf_user->hasCredential(myUser::CREDENTIAL_ADMIN) && (!$compte || !$etablissement)) {
    $etablissement = $sf_user->getEtablissement();
}
if ($sf_user->isAuthenticated() && !$compte && !$etablissement && !count($sf_user->getCredentials())) {
    throw new sfError403Exception("pas de compte");
}
if (($compte && ($compte->statut == CompteClient::STATUT_SUSPENDU)) && !$sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)) {
    throw new sfError403Exception("Compte inactif");
}
?>
<nav id="menu_navigation" class="navbar navbar-default container">
        <div class="navbar-header hidden-lg hidden-md">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo url_for('accueil') ?>"></a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1" style="padding-left: 0;">
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <ul class="nav navbar-nav <?php if($compte): ?>mode-operateur<?php endif; ?>" style="border: 0;">
                <li id="nav_item_operateur" class="<?php if(!$compte): ?>disabled<?php endif; ?>"><a <?php if($compte): ?>onclick="document.location = $(this).parents('ul.mode-operateur').find('li.active a').attr('href');"<?php endif; ?> href="#"><span class="glyphicon glyphicon-user"></span></a></li>
                <li class="<?php if($route instanceof InterfaceDeclarationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDeclarationRoute): ?><?php echo url_for('declaration_etablissement', $etablissement); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">Déclarations</a></li>
                <li class="<?php if($route instanceof InterfaceDocumentsRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceDocumentsRoute): ?><?php echo url_for('pieces_historique', $etablissement); ?><?php else: ?><?php echo url_for('documents'); ?><?php endif; ?>">Documents</a></li>
                <li class="<?php if($route instanceof InterfaceParcellaireRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceParcellaireRoute): ?><?php echo url_for('parcellaire_declarant', $etablissement); ?><?php else: ?><?php echo url_for('parcellaire'); ?><?php endif; ?>">Parcellaire</a></li>
                <li class="<?php if($route instanceof InterfaceFacturationRoute): ?>active<?php endif; ?>"><a href="<?php if($compte  && !$route instanceof InterfaceFacturationRoute): ?><?php echo url_for('facturation_declarant', $compte); ?><?php else: ?><?php echo url_for('facturation'); ?><?php endif; ?>">Facturation</a></li>
                <li class="<?php if($route instanceof InterfaceHabilitationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceHabilitationRoute): ?><?php echo url_for('habilitation_declarant', $etablissement); ?><?php else: ?><?php echo url_for('habilitation'); ?><?php endif; ?>">Habilitations</a></li>
                <li class="<?php if($route instanceof InterfaceCompteRoute && !$route instanceof InterfaceFacturationRoute): ?>active<?php endif; ?>"><a href="<?php if($compte && !$route instanceof InterfaceCompteRoute): ?><?php echo url_for('compte_visualisation', $compte); ?><?php else: ?><?php echo url_for('compte_search'); ?><?php endif; ?>">Contacts</a></li>
            </ul>
            <?php elseif($sf_user->hasCredential(myUser::CREDENTIAL_DREV_ADMIN)): ?>
                <ul class="nav navbar-nav <?php if($compte): ?>mode-operateur<?php endif; ?>" style="border: 0;">
                    <li id="nav_item_operateur" class="<?php if(!$compte): ?>disabled<?php endif; ?>"><a <?php if($compte): ?>onclick="document.location = $(this).parents('ul.mode-operateur').find('li.active a').attr('href');"<?php endif; ?> href="#"><span class="glyphicon glyphicon-user"></span></a></li>
                    <li class="<?php if($route instanceof InterfaceDeclarationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDeclarationRoute): ?><?php echo url_for('declaration_etablissement', $etablissement); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">Déclarations</a></li>
                    <li class="<?php if($route instanceof InterfaceDocumentsRoute): ?>active<?php endif; ?> <?php if(!$compte): ?>disabled<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceDocumentsRoute): ?><?php echo url_for('pieces_historique', $etablissement); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">Documents</a></li>
                    <li class="<?php if($route instanceof InterfaceFacturationRoute): ?>active<?php endif; ?> <?php if(!$compte): ?>disabled<?php endif; ?>"><a href="<?php if($compte  && !$route instanceof InterfaceFacturationRoute): ?><?php echo url_for('facturation_declarant', $compte); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">Facturation</a></li>
                    <li class="<?php if($route instanceof InterfaceHabilitationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceHabilitationRoute): ?><?php echo url_for('habilitation_declarant', $etablissement); ?><?php else: ?><?php echo url_for('habilitation'); ?><?php endif; ?>">Habilitations</a></li>
                    <li class="<?php if($route instanceof InterfaceParcellaireRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceParcellaireRoute): ?><?php echo url_for('parcellaire_declarant', $etablissement); ?><?php else: ?><?php echo url_for('parcellaire'); ?><?php endif; ?>">Parcellaire</a></li>
                    <li class="<?php if($route instanceof InterfaceCompteRoute): ?>active<?php endif; ?> <?php if(!$compte): ?>disabled<?php endif; ?>"><a href="<?php if($compte && !$route instanceof InterfaceCompteRoute): ?><?php echo url_for('compte_visualisation', $compte); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">Contacts</a></li>
                </ul>
            <?php elseif($sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION)): ?>
                <ul class="nav navbar-nav <?php if($compte): ?>mode-operateur<?php endif; ?>" style="border: 0;">
                    <li class="<?php if($route instanceof InterfaceDocumentsRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceDocumentsRoute): ?><?php echo url_for('pieces_historique', $etablissement); ?><?php else: ?><?php echo url_for('documents'); ?><?php endif; ?>">Documents</a></li>
                    <li class="<?php if($route instanceof InterfaceHabilitationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceHabilitationRoute): ?><?php echo url_for('habilitation_declarant', $etablissement); ?><?php else: ?><?php echo url_for('habilitation'); ?><?php endif; ?>">Habilitations</a></li>
                    <?php if(SocieteConfiguration::getInstance()->isVisualisationTeledeclaration()): ?>
                    <li class="<?php if($route instanceof InterfaceParcellaireRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceParcellaireRoute): ?><?php echo url_for('parcellaire_declarant', $etablissement); ?><?php else: ?><?php echo url_for('parcellaire'); ?><?php endif; ?>">Parcellaire</a></li>
                    <li class="<?php if($route instanceof InterfaceCompteRoute): ?>active<?php endif; ?>"><a href="<?php if($compte && !$route instanceof InterfaceCompteRoute): ?><?php echo url_for('compte_visualisation', $compte); ?><?php else: ?><?php echo url_for('habilitation'); ?><?php endif; ?>">Contacts</a></li>
                    <?php endif; ?>
                </ul>
            <?php elseif($sf_user->isAuthenticated() && $etablissement): ?>
                <ul class="nav navbar-nav <?php if($compte): ?>mode-operateur<?php endif; ?>" style="border: 0;">
                    <li class="<?php if($route instanceof InterfaceDeclarationRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>">Déclarations</a></li>
                    <li class="<?php if($route instanceof InterfaceDocumentsRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('pieces_historique', $etablissement); ?>">Documents</a></li>
                    <?php if(SocieteConfiguration::getInstance()->isVisualisationTeledeclaration()): ?>
                    <li class="<?php if($route instanceof InterfaceParcellaireRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire</a></li>
                    <?php endif; ?>
                    <li class="<?php if($route instanceof InterfaceFacturationRoute): ?>active<?php endif; ?>"><a href="<?php if($compte  && !$route instanceof InterfaceFacturationRoute): ?><?php echo url_for('facturation_declarant', $compte); ?><?php endif; ?>">Facturation</a></li>
                    <?php if(SocieteConfiguration::getInstance()->isVisualisationTeledeclaration()): ?>
                    <li class="<?php if($route instanceof InterfaceHabilitationRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('habilitation_declarant', $etablissement); ?>">Habilitations</a></li>
                    <li class="<?php if($route instanceof InterfaceCompteRoute &&  !$route instanceof FacturationDeclarantRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('compte_visualisation', $compte ?: $etablissement); ?>">Contacts</a></li>
                    <?php endif; ?>
                    <li class="<?php if(preg_match('/compte/', $route->getParameters()['module'])): ?>active<?php endif; ?>"><a tabindex="-1" href="<?php echo url_for("compte_teledeclarant_modification") ?>" title="Mon compte">Mon compte</a></li>
                </ul>
            <?php endif; ?>
            <ul class="nav navbar-nav navbar-right">
                <?php if(NavConfiguration::getInstance()->hasStatLinks()): ?>
                <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-stats"></span><span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <?php foreach(NavConfiguration::getInstance()->getStatLinks() as $i => $navItem): ?>
                     <?php if($i > 0 && isset($navItem['title'])): ?><li role="separator" class="divider"></li><?php endif; ?>
                     <li>
                         <a href="<?php echo $navItem['url'] ?>">
                             <?php if(isset($navItem['icon'])): ?><span class="glyphicon glyphicon-<?php echo $navItem['icon'] ?>"></span><?php endif; ?>
                             <?php if(isset($navItem['title'])): ?><strong><?php endif; ?><?php echo $navItem['name'] ?><?php if(isset($navItem['title'])): ?></strong><?php endif; ?>
                         </a>
                    </li>
                    <?php endforeach; ?>
                  </ul>
                </li>
                <?php endif; ?>
                <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
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
