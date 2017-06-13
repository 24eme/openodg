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

<ul class="nav nav-tabs <?php if(isset($hideIfSmall)): ?>hidden-xs hidden-sm<?php endif; ?>">
    <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
    <li class="<?php if($route instanceof InterfaceDeclarationRoute): ?>active<?php endif; ?>"><a href="<?php if($etablissement): ?><?php echo url_for('declarations', $etablissement); ?><?php else: ?><?php echo url_for('admin'); ?><?php endif; ?>">Déclarations</a></li>
    <li class="<?php if($route instanceof InterfaceFacturationRoute): ?>active<?php endif; ?>"><a href="<?php if($compte): ?><?php echo url_for('facturation_declarant', $compte); ?><?php else: ?><?php echo url_for('facturation'); ?><?php endif; ?>">Facturation</a></li>
    <li class="<?php if($route instanceof InterfaceDegustationGeneralRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
    <?php endif; ?>
    <li class="<?php if($route instanceof InterfaceConstatsRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('constats',array('jour' => date('Y-m-d'))); ?>">Constats</a></li>
    <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
    <?php endif; ?>
    <li class="<?php if($route instanceof InterfaceContactsRoute): ?>active<?php endif; ?>"><a href="<?php if($compte): ?><?php echo url_for('compte_visualisation_admin', $compte); ?><?php else: ?><?php echo url_for('compte_recherche'); ?><?php endif; ?>">Contacts</a></li>
        <li class="pull-right <?php if($route instanceof InterfaceExportRoute): ?>active<?php endif; ?>"><a href="<?php echo url_for('export'); ?>">Export</a></li>
</ul>
