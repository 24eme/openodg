<?php use_helper('Date'); ?>

<?php if (!EtablissementSecurity::getInstance($sf_user, $etablissement->getRawValue())->isAuthorized(EtablissementSecurity::DECLARANT_DREV) && (!$drev || !$sf_user->isAdmin())): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($drev && $drev->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">
        <div class="panel-heading">
            <h3>Revendication des appellations viticoles <?php echo $periode; ?></h3>
        </div>
        <?php if ($drev && $drev->validation): ?>
            <div class="panel-body">
                <p>Votre déclaration de revendication viticole a été validée pour cette année.</p>
            </div>
            <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('drev_visualisation', $drev) ?>">Visualiser</a>
                </p>
                <?php if (DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::DEVALIDATION)): ?>
                    <p>
                        <a onclick='return confirm("Êtes vous sûr de vouloir dévalider cette déclaration ?");' class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('drev_devalidation', $drev) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php elseif ($drev): ?>
            <div class="panel-body">
                <p>Votre déclaration de revendication viticole a été débutée pour cette année sans avoir été validée.</p>
            </div>
            <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drev_edit', $drev) ?>"><?php if($drev->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer la saisie papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
                </p>
                <p>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('drev_delete', $drev) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </p>
            </div>
        <?php elseif (!DRevClient::getInstance()->isOpen()): ?>
            <div class="panel-body">
                <?php if(date('Y-m-d') > DRevClient::getInstance()->getDateOuvertureFin()): ?>
                <p>Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'AVA.</p>
                <?php else: ?>
                <p>Le Téléservice sera ouvert à partir du <?php echo format_date(DRevClient::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
                <?php endif; ?>
            </div>
            <div class="panel-bottom">
                <?php if ($sf_user->isAdmin()): ?>
                    <p>
                        <a class="btn btn-lg btn-default btn-block" href="<?php echo url_for('drev_create', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>">Démarrer la télédéclaration</a>
                    </p>
                    <p>
                        <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('drev_create_papier', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <p>Votre déclaration de revendication viticole pour cette année n'a pas encore été déclarée.</p>
            </div>
            <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drev_create', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>">Démarrer la télédéclaration</a>
                </p>
                <?php if ($sf_user->isAdmin()): ?>
                    <p>
                        <a class="btn btn-xs btn-warning btn-block pull-right" href="<?php echo url_for('drev_create_papier', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
