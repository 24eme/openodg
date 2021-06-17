<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($intentionCremant && $intentionCremant->validation): ?>panel-success<?php else: ?>panel-primary<?php endif; ?>">
        <div class="panel-heading">
        <h3>Intention&nbsp;de production<br />AOC Crémant d'Alsace <?php echo $campagne ?>&nbsp;</h3>
        </div>
            <?php if ($intentionCremant && $intentionCremant->validation): ?>
        <div class="panel-body">
                <p>Vous avez déjà validé votre déclaration d'intention de production pour cette année.</p>
        </div>
        <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('parcellaire_visualisation', $intentionCremant) ?>">Visualiser</a>
                </p>
                <?php if($sf_user->isAdmin()): ?>
                <p>
                    <a onclick='return confirm("Êtes vous sûr de vouloir dévalider cette déclaration ?");' class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('parcellaire_devalidation', $intentionCremant) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
                </p>
                <?php endif; ?>
        </div>
        <?php elseif ($intentionCremant):  ?>
        <div class="panel-body">
                <p>Vous avez déjà débuté votre déclaration d'intention de production pour cette année sans la valider.</p>
        </div>
        <div class="panel-bottom">
            <p>
                <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('parcellaire_edit', $intentionCremant) ?>"><?php if($intentionCremant->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer la saisie papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
            </p>
            <p>
                <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-danger pull-right" href="<?php echo url_for('parcellaire_delete', $intentionCremant) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
            </p>
        </div>
        <?php elseif (!ParcellaireAffectationClient::getInstance()->isOpen(ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT)): ?>
            <div class="panel-body">
                <?php if(date('Y-m-d') > ParcellaireAffectationClient::getInstance()->getDateOuvertureFin(ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT)): ?>
                <p>Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'AVA.</p>
                <?php else: ?>
                <p>Le Téléservice sera ouvert à partir du <?php echo format_date(ParcellaireAffectationClient::getInstance()->getDateOuvertureDebut(ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT), "D", "fr_FR") ?>.</p>
                <?php endif; ?>
            </div>
            <div class="panel-bottom">
                <?php if ($sf_user->isAdmin()): ?>
                    <p>
                        <a class="btn btn-lg btn-default btn-block" href="<?php echo url_for('intention_cremant_create', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>">Démarrer la télédéclaration</a>
                    </p>
                    <p>
                        <a class="btn btn-xs btn-warning btn-block" href="<?php echo url_for('intention_cremant_create_papier', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php else:  ?>
        <div class="panel-body">
                <p>Aucune déclaration d'intention de production n'a été débutée vous concernant cette année</p>
        </div>
        <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('intention_cremant_create', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>">Démarrer la télédéclaration</a>
                </p>

                <?php if ($sf_user->isAdmin()): ?>
                    <p>
                        <a class="btn btn-xs btn-block btn-warning pull-right" href="<?php echo url_for('intention_cremant_create_papier', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    </p>
                <?php endif; ?>
        </div>
            <?php endif; ?>
    </div>
</div>
