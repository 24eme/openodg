<div class="col-xs-4">
        <?php if (!$parcellaire_cremant_non_ouverte): ?>
            <div class="block_declaration panel <?php if ($parcellaireCremant && $parcellaireCremant->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">     
                <div class="panel-heading">
                <h3>Affectation&nbsp;parcellaire crémant<br/>&nbsp;</h3>
                </div>
                    <?php if ($parcellaireCremant && $parcellaireCremant->validation): ?>
                <div class="panel-body">
                        <p>Vous avez déjà validé votre déclaration d'affectation parcellaire crémant pour cette année.</p>
                    </div>
                <div class="panel-bottom">
                        <p>
                            <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('parcellaire_visualisation', $parcellaireCremant) ?>">Visualiser</a>
                        </p>
                        <?php if($sf_user->isAdmin()): ?>
                        <p>
                            <a class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('parcellaire_devalidation', $parcellaireCremant) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                        </p>
                </div>
                        <?php endif; ?>
                    <?php elseif ($parcellaireCremant):  ?>
                <div class="panel-body">
                        <p>Vous avez déjà débuté votre déclaration d'affectation parcellaire crémant pour cette année sans la valider.</p>
                       </div>
                <div class="panel-bottom">
                    <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('parcellaire_edit', $parcellaireCremant) ?>">Continuer</a>
                        </p>
                        <p>
                            <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('parcellaire_delete', $parcellaireCremant) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                        </p>
                        </div>
                    <?php else:  ?> 
                <div class="panel-body">
                        <p>Aucune déclaration d'affectation parcellaire crémant n'a été débutée vous concernant cette année</p>
                         </div>
                <div class="panel-bottom">
                        <p>
                            <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('parcellaire_cremant_create', $etablissement) ?>">Démarrer</a>
                        </p>
                </div>
                    <?php endif; ?>
            </div>
        <?php else: ?>
            <?php include_partial('parcellaireCremant/parcellaireCremantNonOuvert', array('date_ouverture_parcellaire_cremant' => $date_ouverture_parcellaire_cremant)); ?>
        <?php endif; ?>
</div>
