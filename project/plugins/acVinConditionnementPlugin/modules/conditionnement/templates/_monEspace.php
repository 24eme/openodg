<?php use_helper('Date'); ?>

<?php if (!EtablissementSecurity::getInstance($sf_user, $etablissement->getRawValue())->isAuthorized(EtablissementSecurity::DECLARANT_CONDITIONNEMENT) && (!$conditionnement || !$sf_user->isAdmin() || !$sf_user->hasCondtionnementAdmin())): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Déclaration&nbsp;de&nbsp;Conditionnement</h3>
        </div>
        <?php if ($conditionnement && (ConditionnementClient::getInstance()->isOpen() || $sf_user->isAdmin())): ?>
            <div class="panel-body">
                <p>Une déclaration de conditionnement a été débutée sans avoir été validée.</p>
                <div style="margin-top: 50px;">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('conditionnement_edit', $conditionnement) ?>"><?php if($conditionnement->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer le conditionnement papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('conditionnement_delete', $conditionnement) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
        <?php elseif (!ConditionnementClient::getInstance()->isOpen()): ?>
            <div class="panel-body">
                <?php if(date('Y-m-d') > ConditionnementClient::getInstance()->getDateOuvertureFin()): ?>
                <p>Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'ODG.</p>
                <?php else: ?>
                <p>Le Téléservice sera ouvert à partir du <?php echo format_date(ConditionnementClient::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
                <?php endif; ?>
                <?php if ($sf_user->isAdmin()): ?>
                <div style="margin-top: 50px;">
                    <a class="btn btn-default btn-block" href="<?php echo url_for('conditionnement_create', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>">Démarrer la télédéclaration</a>
                    <a class="btn btn-xs btn-default btn-block" href="<?php echo url_for('conditionnement_create_papier', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir le conditionnement papier</a>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <p>Espace permettant la déclaration de vos conditionnements.</p>
                <div style="margin-top: 50px;">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('conditionnement_create', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>">Démarrer la télédéclaration</a>
                    <?php if ($sf_user->isAdmin() || $sf_user->hasConditionnementAdmin()): ?>
                        <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('conditionnement_create_papier', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir le conditionnement papier</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
