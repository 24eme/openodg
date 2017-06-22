<?php $route = $sf_request->getAttribute('sf_route')->getRawValue(); ?>
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

<nav class="navbar navbar-default" style="margin: 0; border: 0;">
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1" style="padding-left: 0;">
      <ul class="nav navbar-nav" style="border: 0;">
        <li class="<?php if($route instanceof InterfaceDeclarationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDeclarationRoute): ?><?php echo url_for('declaration_etablissement', $etablissement); ?><?php else: ?><?php echo url_for('declaration'); ?><?php endif; ?>">Déclarations</a></li>
        <li class="<?php if($route instanceof InterfaceFacturationRoute): ?>active<?php endif; ?>"><a href="<?php if($compte  && !$route instanceof InterfaceFacturationRoute): ?><?php echo url_for('facturation_declarant', $compte); ?><?php else: ?><?php echo url_for('facturation'); ?><?php endif; ?>">Facturation</a></li>
        <li class="<?php if($route instanceof InterfaceDegustationGeneralRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement && !$route instanceof InterfaceDegustationGeneralRoute): ?><?php echo url_for('degustation_declarant', $etablissement); ?><?php else: ?><?php echo url_for('degustation'); ?><?php endif; ?>">Dégustation</a></li>
        <li class="<?php if($route instanceof InterfaceConstatsRoute): ?>active<?php endif; ?>"><a href="<?php if($compte && !$route instanceof InterfaceConstatsRoute): ?><?php echo url_for('rendezvous_declarant', $compte); ?><?php else: ?><?php echo url_for('constats',array('jour' => date('Y-m-d'))); ?><?php endif; ?>">Constats</a></li>
        <li class="<?php if($route instanceof InterfaceContactsRoute): ?>active<?php endif; ?>"><a href="<?php if($compte && !$route instanceof InterfaceContactsRoute): ?><?php echo url_for('compte_visualisation', $compte); ?><?php else: ?><?php echo url_for('compte_recherche'); ?><?php endif; ?>">Contacts</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li class="<?php if($route instanceof InterfaceExportRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('export'); ?>"><span class="glyphicon glyphicon-export"></span> Export</a></li>
      </ul>
    </div>
</nav>
