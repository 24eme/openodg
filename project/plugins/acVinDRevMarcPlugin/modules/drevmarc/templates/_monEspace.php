<?php use_helper('Date'); ?>

<?php if (!$etablissement->hasFamille(EtablissementClient::FAMILLE_DISTILLATEUR)): ?>
    <?php return; ?>
<?php endif; ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <?php if ($etablissement->hasFamille(EtablissementClient::FAMILLE_DISTILLATEUR)): ?>
        <div class="block_declaration panel <?php if ($drevmarc && $drevmarc->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">
            <div class="panel-heading">
                <h3>Revendication Marc&nbsp;d'Alsace&nbsp;Gw&nbsp;<?php echo $periode; ?></h3>
            </div>
            <?php if ($drevmarc && $drevmarc->validation): ?>
                <div class="panel-body">
                    <p>Votre déclaration de revendication de Marc d'Alsace Gewurztraminer a été validée pour cette année.</p>
                </div>
                <div class="panel-bottom">
                    <p>
                        <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('drevmarc_visualisation', $drevmarc) ?>">Visualiser</a>
                    </p>
                    <?php if (DRevSecurity::getInstance($sf_user, $drevmarc->getRawValue())->isAuthorized(DRevMarcSecurity::DEVALIDATION)): ?>
                        <p>
                            <a onclick='return confirm("Êtes vous sûr de vouloir dévalider cette déclaration ?");' class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('drevmarc_devalidation', $drevmarc) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                        </p>
                <?php endif; ?>
                    </div>
            <?php elseif ($drevmarc): ?>
                <div class="panel-body">
                    <p>Votre déclaration de revendication de Marc d'Alsace Gewurztraminer a été débutée pour cette année mais n'a pas été validée.</p>
                </div>
                <div class="panel-bottom">
                    <p>
                        <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_edit', $drevmarc) ?>"><?php if($drevmarc->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer la saisie papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
                    </p>
                    <p>
                        <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('drevmarc_delete', $drevmarc) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                    </p>
                </div>
            <?php elseif (!DRevMarcClient::getInstance()->isOpen()): ?>
                <div class="panel-body">
                    <?php if(date('Y-m-d') > DRevMarcClient::getInstance()->getDateOuvertureFin()): ?>
                    <p>Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'AVA.</p>
                    <?php else: ?>
                    <p>Le Téléservice sera ouvert à partir du <?php echo format_date(DRevMarcClient::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
                    <?php endif; ?>
                </div>
                <div class="panel-bottom">
                    <?php if ($sf_user->isAdmin()): ?>
                        <p>
                            <a class="btn btn-lg btn-default btn-block" href="<?php echo url_for('drevmarc_create', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>">Démarrer la télédéclaration</a>
                        </p>
                        <p>
                            <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('drevmarc_create_papier', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="panel-body">
                    <p>Votre déclaration de revendication de Marc d'Alsace Gewurztraminer n'a pas été validée pour cette année.</p>
                </div>
                <div class="panel-bottom">
                    <p>
                        <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_create', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>">Démarrer la télédéclaration</a>
                    </p>
                    <?php if ($sf_user->isAdmin()): ?>
                        <p>
                            <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('drevmarc_create_papier', array('sf_subject' => $etablissement, 'campagne' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
