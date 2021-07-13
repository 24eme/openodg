<?php use_helper('Date'); ?>

<?php if (!EtablissementSecurity::getInstance($sf_user, $etablissement->getRawValue())->isAuthorized(EtablissementSecurity::DECLARANT_DREV) && (!$drev || !$sf_user->isAdmin() || !$sf_user->hasDrevAdmin())): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($drev && $drev->validation): ?>panel-success<?php elseif($drev): ?>panel-primary<?php else : ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Revendication&nbsp;des&nbsp;produits&nbsp;<?php echo $periode; ?></h3>
        </div>
        <?php if ($drev && $drev->validation): ?>
            <div class="panel-body">
                <p class="explications">Votre déclaration de revendication a été validée pour cette année.</p>
                <div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('drev_visualisation', $drev) ?>">Visualiser la déclaration</a>
                </div>
            </div>
        <?php elseif ($drev && (DRevClient::getInstance()->isOpen() || $sf_user->isAdmin() || $sf_user->hasDrevAdmin())): ?>
            <div class="panel-body">
                <p class="explications">Votre déclaration de revendication de cette année a été débutée sans avoir été validée.</p>
                <div class="actions">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('drev_edit', $drev) ?>"><span class="glyphicon glyphicon-pencil"></span> Reprendre la saisie</a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('drev_delete', $drev) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
        <?php elseif (!DRevClient::getInstance()->isOpen()): ?>
            <div class="panel-body">
                <?php if(date('Y-m-d') > DRevClient::getInstance()->getDateOuvertureFin()): ?>
                <p class="explications">Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'ODG.</p>
                <?php else: ?>
                <p class="explications">Le Téléservice sera ouvert à partir du <?php echo format_date(DRevClient::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
                <?php endif; ?>
                <div class="actions">
                <?php if ($sf_user->isAdmin()): ?>
                    <a class="btn btn-default btn-block" href="<?php echo url_for('drev_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                    <a class="btn btn-xs btn-default btn-block" href="<?php echo url_for('drev_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisie papier</a>
                <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <p class="explications">Votre déclaration de revendication viticole pour cette année n'a pas encore été déclarée.</p>
                <div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('drev_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                    <?php if ($sf_user->isAdmin() || $sf_user->hasDrevAdmin()): ?>
                        <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('drev_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisie papier</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
            <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'drev')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
        </div>
    </div>
</div>
