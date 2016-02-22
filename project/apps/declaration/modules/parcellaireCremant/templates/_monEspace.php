<div class="col-xs-4">
    <div class="block_declaration panel <?php if ($parcellaireCremant && $parcellaireCremant->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">     
        <div class="panel-heading">
        <h3>Affectation&nbsp;parcellaire Crémant <?php echo ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext(); ?>&nbsp;</h3>
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
                <?php endif; ?>
        </div>
        <?php elseif ($parcellaireCremant):  ?>
        <div class="panel-body">
                <p>Vous avez déjà débuté votre déclaration d'affectation parcellaire crémant pour cette année sans la valider.</p>
        </div>
        <div class="panel-bottom">
            <p>
                    <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('parcellaire_edit', $parcellaireCremant) ?>"><?php if($parcellaireCremant->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer la saisie papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
            </p>
            <p>
                <a class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('parcellaire_delete', $parcellaireCremant) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
            </p>
        </div>
        <?php elseif (!ParcellaireClient::getInstance()->isOpen(true)): ?>
            <div class="panel-body">
                <?php if(date('Y-m-d') > ParcellaireClient::getInstance()->getDateOuvertureFin(true)): ?>
                <p>Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'AVA.</p>
                <?php else: ?>
                <p>Le Téléservice sera ouvert à partir du <?php echo format_date(ParcellaireClient::getInstance()->getDateOuvertureDebut(true), "D", "fr_FR") ?>.</p>
                <?php endif; ?>
            </div>
            <div class="panel-bottom">
                <?php if ($sf_user->isAdmin()): ?>
                    <p>
                        <a class="btn btn-lg btn-default btn-block" href="<?php echo url_for('parcellaire_cremant_create', $etablissement) ?>">Démarrer la télédéclaration</a>
                    </p>
                    <p>
                        <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('parcellaire_cremant_create_papier', $etablissement) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php else:  ?> 
        <div class="panel-body">
                <p>Aucune déclaration d'affectation parcellaire crémant n'a été débutée vous concernant cette année</p>
        </div>
        <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('parcellaire_cremant_create', $etablissement) ?>">Démarrer la télédéclaration</a>
                </p>

                <?php if ($sf_user->isAdmin()): ?>
                    <p>
                        <a class="btn btn-xs btn-block btn-warning pull-right" href="<?php echo url_for('parcellaire_cremant_create_papier', $etablissement) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    </p>
                <?php endif; ?>
        </div>
            <?php endif; ?>
    </div>
</div>
