<?php if (!count($drevmarcsHistory) && !$etablissement->hasFamille(EtablissementClient::FAMILLE_DISTILLATEUR)): ?>
    <?php return; ?>
<?php endif; ?>

<div class="col-xs-4">
    <?php if ($etablissement->hasFamille(EtablissementClient::FAMILLE_DISTILLATEUR)): ?>
        <?php if (!$drevmarc_non_ouverte): ?>
            <div class="block_declaration panel <?php if ($drevmarc && $drevmarc->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">     
                <div class="panel-heading">
                    <h3>Revendication Marc&nbsp;d'Alsace<?php echo ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(); ?></h3>
                </div>
                <div class="panel-body">
                    <?php if ($drevmarc && $drevmarc->validation): ?>
                        <p>
                            <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('drevmarc_visualisation', $drevmarc) ?>">Visualiser</a>
                        </p>
                        <?php if($sf_user->isAdmin()): ?>
                        <p>
                            <a class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('drevmarc_devalidation', $drevmarc) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                        </p>
                        <?php endif; ?>
                    <?php elseif ($drevmarc): ?>
                        <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_edit', $drevmarc) ?>">Continuer</a>
                        </p>
                        <p>
                            <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('drevmarc_delete', $drevmarc) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                        </p>
                    <?php else: ?>
                        <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_create', $etablissement) ?>">Démarrer</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <?php include_partial('drevmarcNonOuvert', array('date_ouverture_drevmarc' => $date_ouverture_drevmarc)); ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
