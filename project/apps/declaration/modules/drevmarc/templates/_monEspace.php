<?php if (!count($drevmarcsHistory) && !$etablissement->hasFamille(EtablissementClient::FAMILLE_DISTILLATEUR)): ?>
    <?php return; ?>
<?php endif; ?>

<div class="col-xs-4">
    <?php if ($etablissement->hasFamille(EtablissementClient::FAMILLE_DISTILLATEUR)): ?>
        <?php if (!$drevmarc_non_ouverte): ?>
            <div class="block_declaration panel <?php if ($drevmarc && $drevmarc->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">     
                <div class="panel-heading">
                    <h3>Revendication Marc&nbsp;d'Alsace&nbsp;Gw&nbsp;<?php echo ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(); ?></h3>
                </div>
                <?php if ($drevmarc && $drevmarc->validation): ?>
                    <div class="panel-body">
                        <p>Votre déclaration de revendication de Marc d'Alsace Gewurztraminer a été validée pour cette année.</p>
                    </div>
                    <div class="panel-bottom">
                        <p>
                            <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('drevmarc_visualisation', $drevmarc) ?>">Visualiser</a>
                        </p>
                        <?php if ($sf_user->isAdmin()): ?>
                            <p>
                                <a class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('drevmarc_devalidation', $drevmarc) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                            </p>
                    <?php endif; ?>
                        </div>
                <?php elseif ($drevmarc): ?>
                    <div class="panel-body">
                        <p>Votre déclaration de revendication de Marc d'Alsace Gewurztraminer a été débutée pour cette année mais n'a pas été validée.</p>
                    </div>
                    <div class="panel-bottom">
                        <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_edit', $drevmarc) ?>">Continuer</a>
                        </p>
                        <p>
                            <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('drevmarc_delete', $drevmarc) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="panel-body">
                        <p>Votre déclaration de revendication de Marc d'Alsace Gewurztraminer n'a pas été validée pour cette année.</p>
                    </div>
                    <div class="panel-bottom">  
                        <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_create', $etablissement) ?>">Démarrer</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php include_partial('drevmarcNonOuvert', array('date_ouverture_drevmarc' => $date_ouverture_drevmarc)); ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
