<?php $route = $sf_request->getAttribute('sf_route')->getRawValue(); ?>
<?php $etablissement = null ?>
<?php $compte = null ?>

<?php if($route instanceof EtablissementRoute): ?>
    <?php $etablissement = $route->getEtablissement(); ?>
    <?php $compte = $etablissement->getMasterCompte(); ?>
<?php endif; ?>
<?php if($route instanceof CompteRoute): ?>
    <?php $compte = $route->getCompte(); ?>
    <?php $etablissement = $compte->getEtablissementObj(); ?>
<?php endif; ?>
<?php if($route instanceof SocieteRoute): ?>
    <?php $etablissement = $route->getEtablissement(); ?>
    <?php $compte = $etablissement->getMasterCompte(); ?>
<?php endif; ?>
<nav id="menu_navigation" class="navbar navbar-default">
    <div class="container">
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
            <ul class="nav navbar-nav <?php if($compte): ?>mode-operateur<?php endif; ?>" style="border: 0;">
                <li id="nav_item_operateur" class="<?php if(!$compte): ?>disabled<?php endif; ?>"><a onclick="document.location = $(this).parents('ul.mode-operateur').find('li.active a').attr('href');" href="#"><span class="glyphicon glyphicon-user"></span></a></li>
                <li class="<?php if($route instanceof InterfaceDeclarationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDeclarationRoute): ?><?php echo url_for('declaration_etablissement', $etablissement); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">Déclarations</a></li>
                <li class="<?php if($route instanceof InterfaceFacturationRoute): ?>active<?php endif; ?>"><a href="<?php if($compte  && !$route instanceof InterfaceFacturationRoute): ?><?php echo url_for('facturation_declarant', $compte); ?><?php else: ?><?php echo url_for('facturation'); ?><?php endif; ?>">Facturation</a></li>
                <li class="<?php if($route instanceof SearchRoute || $route instanceof SocieteRoute): ?>active<?php endif; ?>"><a href="<?php if($compte && !$route instanceof InterfaceContactsRoute): ?><?php echo url_for('compte_visualisation', $compte); ?><?php else: ?><?php echo url_for('compte_search'); ?><?php endif; ?>">Contacts</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="<?php if($route instanceof InterfaceExportRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('export'); ?>"><span class="glyphicon glyphicon-export"></span> Export</a></li>
            </ul>
        </div>
    </div>
</nav>
