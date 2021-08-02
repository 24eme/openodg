<?php use_helper('Date'); ?>

<?php if (!$etablissement->hasFamille(EtablissementClient::FAMILLE_DISTILLATEUR)): ?>
    <?php return; ?>
<?php endif; ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($travauxmarc && $travauxmarc->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">
        <div class="panel-heading">
            <h3>Ouverture&nbsp;travaux&nbsp;de&nbsp;distillation Marc&nbsp;d'Alsace&nbsp;Gw&nbsp;<?php echo $periode; ?></h3>
        </div>
        <?php if ($travauxmarc && $travauxmarc->validation): ?>
            <div class="panel-body">
                <p>Votre déclaration d' a été validée pour cette année.</p>
            </div>
            <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('travauxmarc_visualisation', $travauxmarc) ?>">Visualiser</a>
                </p>
                <?php if (DRevSecurity::getInstance($sf_user, $travauxmarc->getRawValue())->isAuthorized(DRevMarcSecurity::DEVALIDATION)): ?>
                    <p>
                        <a onclick='return confirm("Êtes vous sûr de vouloir dévalider cette déclaration ?");' class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('travauxmarc_devalidation', $travauxmarc) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                    </p>
            <?php endif; ?>
                </div>
        <?php elseif ($travauxmarc): ?>
            <div class="panel-body">
                <p>Votre déclaration de revendication de Marc d'Alsace Gewurztraminer a été débutée pour cette année mais n'a pas été validée.</p>
            </div>
            <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('travauxmarc_edit', $travauxmarc) ?>"><?php if($travauxmarc->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer la saisie papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
                </p>
                <p>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('travauxmarc_delete', $travauxmarc) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </p>
            </div>
        <?php elseif (!TravauxMarcClient::getInstance()->isOpen()): ?>
            <div class="panel-body">
                <?php if(date('Y-m-d') > TravauxMarcClient::getInstance()->getDateOuvertureFin()): ?>
                <p>Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'AVA.</p>
                <?php else: ?>
                <p>Le Téléservice sera ouvert à partir du <?php echo format_date(TravauxMarcClient::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
                <?php endif; ?>
            </div>
            <div class="panel-bottom">
                <?php if ($sf_user->isAdmin()): ?>
                    <p>
                        <a class="btn btn-lg btn-default btn-block" href="<?php echo url_for('travauxmarc_create', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>">Démarrer la télédéclaration</a>
                    </p>
                    <p>
                        <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('travauxmarc_create_papier', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <p>Votre déclaration d'ouverture de travaux de distillation n'a pas été validée pour cette année.</p>
            </div>
            <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('travauxmarc_create', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>">Démarrer la télédéclaration</a>
                </p>
                <?php if ($sf_user->isAdmin()): ?>
                    <p>
                        <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('travauxmarc_create_papier', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
