<div id="bg-page" class="hidden-xs hidden-sm">
    <img src="/images/bg/bg_global.jpg" alt="" />
</div>

<header id="header" class="container <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN) || $sf_params->get('modeMobile')): ?>hidden-xs hidden-sm<?php endif; ?>" role="banner">
    <div id="logo">
        <a href="<?php echo url_for('accueil') ?>" title="AVA - Association des viticulteurs d'alsace | Retour à la page d'accueil">
            <img src="/images/logo_site.png" alt="AVA - Association des viticulteurs d'alsace" />
        </a>
    </div>
    <h1 id="header_titre" class="sr-only">Portail de l'association<br /> des viticulteurs d'alsace</h1>
    <?php use_helper('Text'); ?>
    <?php if($sf_user->isAuthenticated()): ?>
    <?php if($sf_user->getCompte()): ?>
    <nav class="<?php if($sf_user->getEtablissement()): ?>bloc-right<?php endif; ?>" id="navigation-admin" role="navigation">
        <span class="profile-name"><?php echo $sf_user->getCompte()->nom ?></span>
        <ul>
            <li><a href="<?php echo url_for('redirect_to_mon_compte_civa'); ?>">Mon compte</a></li>
            <li><a href="<?php echo url_for('auth_logout') ?>">Déconnexion</a></li>
        </ul>
    </nav>
    <?php endif; ?>
    <?php if($sf_user->getEtablissement()): ?>
    <nav id="navigation" role="navigation">
        <span class="profile-name"><?php echo str_replace(" ", "&nbsp;", truncate_text(preg_replace('/(EARL|SCEA|SARL|SAS|SA|GAEC|Distillerie)(.*)/', "$2", $sf_user->getEtablissement()->nom),30)); ?></span>
        <ul>
            <li><a href="<?php echo url_for('accueil') ?>">Mes déclarations AVA</a></li>
            <li><a href="<?php echo sfConfig::get('app_url_civa') ?>">Mon espace CIVA</a></li>
            <li><a href="<?php echo url_for('mon_compte'); ?>">Mon compte</a></li>
            <?php if(!$sf_user->getCompte()): ?>
            <li><a href="<?php echo url_for('auth_logout') ?>">Déconnexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
    <?php if($sf_user->isAuthenticated() && !$sf_user->getCompte() && !$sf_user->getEtablissement()): ?>
        <nav id="navigation" role="navigation">
            <ul>
                <li><a href="<?php echo url_for('auth_logout') ?>">Déconnexion</a></li>
            </ul>
        </nav>
    <?php endif; ?>
</header>
