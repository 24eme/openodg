<div class="col-xs-4">
        <?php if (!$parcellaire_non_ouverte): ?>
            <div class="block_declaration panel <?php if ($parcellaire && $parcellaire->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">     
                <div class="panel-heading">
                <h3>Affectation&nbsp;parcellaire&nbsp;<?php echo ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()+1; ?><br/>&nbsp;</h3>
                </div>
                    <?php if ($parcellaire && $parcellaire->validation): ?>
                <div class="panel-body">
                        <p>Vous avez déjà validé votre déclaration d'affectation parcellaire pour cette année.</p>
                    </div>
                <div class="panel-bottom">
                        <p>
                            <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('parcellaire_visualisation', $parcellaire) ?>">Visualiser</a>
                        </p>
                        <?php if($sf_user->isAdmin()): ?>
                        <p>
                            <a class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('parcellaire_devalidation', $parcellaire) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                        </p>
                </div>
                        <?php endif; ?>
                    <?php elseif ($parcellaire):  ?>
                <div class="panel-body">
                        <p>Vous avez déjà débuté votre déclaration d'affectation parcellaire pour cette année sans la valider.</p>
                       </div>
                <div class="panel-bottom">
                    <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('parcellaire_edit', $parcellaire) ?>">Continuer</a>
                        </p>
                        <p>
                            <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('parcellaire_delete', $parcellaire) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                        </p>
                        </div>
                    <?php else:  ?> 
                <div class="panel-body">
                        <p>Aucune déclaration d'affectation parcellaire n'a été débutée vous concernant cette année</p>
                         </div>
                <div class="panel-bottom">
                        <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('parcellaire_create', $etablissement) ?>">Démarrer</a>
                        </p>
                </div>
                    <?php endif; ?>
            </div>
        <?php else: ?>
            <?php include_partial('parcellaire/parcellaireNonOuvert', array('date_ouverture_parcellaire' => $date_ouverture_parcellaire)); ?>
        <?php endif; ?>
</div>
