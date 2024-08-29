<?php $route = ($sf_request->getAttribute('sf_route')) ? $sf_request->getAttribute('sf_route')->getRawValue() : null; ?>
<?php $etablissement = null ?>
<?php $compte = null ?>

<?php if($route instanceof EtablissementRoute): ?>
    <?php $etablissement = $route->getEtablissement(); ?>
    <?php $compte = $etablissement->getCompte(); ?>
<?php endif; ?>
<?php if($route instanceof CompteRoute): ?>
    <?php $compte = $route->getCompte(); ?>
    <?php $etablissement = $compte->getEtablissementObj(); ?>
<?php endif; ?>
<?php if(!$etablissement && $sf_user->getEtablissement()): ?>
    <?php $etablissement = $sf_user->getEtablissement(); ?>
    <?php $compte = $etablissement->getCompte(); ?>
<?php endif; ?>

<nav id="menu_navigation" class="navbar navbar-default">
    <div class="navbar-header hidden-lg hidden-md">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php echo url_for('accueil') ?>"><img src="/images/logo_ava.svg" style="height:28px;" alt="" /></a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1" style="padding-left: 0;">
        <?php if($sf_user->isAdmin()): ?>
        <ul class="nav navbar-nav <?php if($compte): ?>mode-operateur<?php endif; ?>" style="border: 0;">
            <li id="nav_item_operateur" class="<?php if(!$compte): ?>disabled<?php endif; ?>"><a onclick="document.location = $(this).parents('ul.mode-operateur').find('li.active a').attr('href');" href="#"><span class="glyphicon glyphicon-user"></span></a></li>
            <li class="<?php if($route instanceof InterfaceDeclarationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDeclarationRoute): ?><?php echo url_for('declaration_etablissement', $etablissement); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">Déclarations</a></li>
            <li class="<?php if($route instanceof InterfaceDocumentsRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDocumentsRoute): ?><?php echo url_for('pieces_historique', $etablissement); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">Docs</a></li>
            <li class="<?php if($route instanceof InterfaceFacturationRoute): ?>active<?php endif; ?>"><a href="<?php if($compte  && !$route instanceof InterfaceFacturationRoute): ?><?php echo url_for('facturation_declarant', ['identifiant' => $compte->_id]); ?><?php else: ?><?php echo url_for('facturation'); ?><?php endif; ?>">Facturation</a></li>
            <li class="<?php if($route instanceof InterfaceDegustationGeneralRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDegustationGeneralRoute): ?><?php echo url_for('degustation_declarant', $etablissement); ?><?php else: ?><?php echo url_for('degustation'); ?><?php endif; ?>">Dégustation</a></li>
            <li class="<?php if($route instanceof InterfaceConstatsRoute): ?>active<?php endif; ?>"><a href="<?php if($compte && !$route instanceof InterfaceConstatsRoute): ?><?php echo url_for('rendezvous_declarant', $compte); ?><?php else: ?><?php echo url_for('constats',array('jour' => date('Y-m-d'))); ?><?php endif; ?>">Constats</a></li>
            <?php  if(in_array('habilitation', sfConfig::get('sf_enabled_modules'))): ?>
            <li class="<?php if($route instanceof InterfaceHabilitationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement  && !$route instanceof InterfaceHabilitationRoute): ?><?php echo url_for('habilitation_declarant', $etablissement); ?><?php else: ?><?php echo url_for('habilitation'); ?><?php endif; ?>">Habilitations</a></li>
            <?php endif; ?>
            <li class="<?php if($route instanceof InterfaceParcellaireRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceParcellaireRoute): ?><?php echo url_for('parcellaire_declarant', $etablissement); ?><?php else: ?><?php echo url_for('parcellaire'); ?><?php endif; ?>">Parcellaire</a></li>
            <li class="<?php if($route instanceof InterfaceContactsRoute): ?>active<?php endif; ?>"><a href="<?php if($compte && !$route instanceof InterfaceContactsRoute): ?><?php echo url_for('compte_visualisation', $compte); ?><?php else: ?><?php echo url_for('compte_recherche'); ?><?php endif; ?>">Contacts</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog"></span> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                <li><a href="/exports">Export</a></li>
                <li class="<?php if($route instanceof InterfaceExportRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('export'); ?>">Génération</a></li>
                <li><a href="<?php echo url_for('facturation_template_last') ?>">Template de facturation</a></li>
                </ul>
            </li>
        </ul>
    <?php else: ?>
            <ul class="nav navbar-nav <?php if($compte): ?>mode-operateur<?php endif; ?>" style="border: 0;">
                <li class="<?php if($route instanceof InterfaceDeclarationRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>">Déclarations</a></li>
                <li class="<?php if($route instanceof InterfaceDocumentsRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('pieces_historique', $etablissement); ?>">Documents</a></li>
                <li class="<?php if($route instanceof InterfaceFacturationRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('facturation_declarant', ['identifiant' => $compte->identifiant]); ?>">Facturation</a></li>
                <li class="<?php if($route instanceof InterfaceHabilitationRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('habilitation_declarant', $etablissement); ?>">Habilitations</a></li>
                <li class="<?php if($route instanceof InterfaceParcellaireRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire</a></li>
            </ul>
        <?php endif; ?>
    </div>
</nav>
