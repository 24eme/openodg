<?php if (!EtablissementSecurity::getInstance($sf_user, $etablissement)->isAuthorized(EtablissementSecurity::DECLARANT_DREV)): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-xs-4">
    <?php if ($etablissement->hasFamille(EtablissementClient::FAMILLE_VINIFICATEUR)): ?>
        <?php if (!$drev_non_ouverte): ?>
            <div class="block_declaration panel <?php if ($drev && $drev->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">
                <div class="panel-heading">
                    <h3>Appellations&nbsp;Viticoles&nbsp;<?php echo ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(); ?></h3>
                </div>
                <div class="panel-body">
                    <?php if ($drev && $drev->validation): ?>
                        <p>
                            <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('drev_visualisation', $drev) ?>">Visualiser</a>
                        </p>
                        <?php if($sf_user->isAdmin()): ?>
                        <p>
                            <a class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('drev_devalidation', $drev) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                        </p>
                    <?php endif; ?>
                    <?php elseif ($drev): ?>
                        <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drev_edit', $drev) ?>">Continuer</a>
                        </p>
                        <p>
                            <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('drev_delete', $drev) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                        </p>
                    <?php else: ?>
                        <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drev_create', $etablissement) ?>">Démarrer</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>       

        <?php else: ?>
            <?php include_partial('drevNonOuvert', array('date_ouverture_drev' => $date_ouverture_drev)); ?>
        <?php endif; ?>

    <?php endif; ?>
</div>