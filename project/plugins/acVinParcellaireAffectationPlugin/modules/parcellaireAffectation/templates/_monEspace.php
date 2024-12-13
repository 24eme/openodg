<?php if(strpos($etablissement->famille, 'PRODUCTEUR') === false): return; endif; ?>
<?php use_helper('Date'); ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($parcellaireAffectation && $parcellaireAffectation->validation): ?>panel-success<?php elseif ($parcellaireAffectation): ?>panel-primary<?php else: ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Déclaration d'affectation parcellaire <?php echo $periode ?></h3>
        </div>
        <?php if ($parcellaireAffectation && $parcellaireAffectation->validation): ?>
        <div class="panel-body">
            <p class="explications">Vous avez déjà validé votre Déclaration d'affectation parcellaire.</p>
            <div class="actions">
                <a class="btn btn-block btn-default" href="<?php echo url_for('parcellaireaffectation_visualisation', $parcellaireAffectation) ?>">Visualiser</a>
           		<?php if($sf_user->isAdmin()): ?>
                <a onclick='return confirm("Êtes vous sûr de vouloir dévalider cette déclaration ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('parcellaireaffectation_devalidation', $parcellaireAffectation) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
            	<?php endif; ?>
            </div>
        </div>
        <?php elseif ($parcellaireAffectation):  ?>
            <div class="panel-body">
                <p class="explications">Vous avez débuté votre Déclaration d'affectation parcellaire sans la valider.</p>
                <div class="actions">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('parcellaireaffectation_edit', $parcellaireAffectation) ?>">Continuer la télédéclaration</a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-default btn-block" href="<?php echo url_for('parcellaireaffectation_delete', $parcellaireAffectation) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
          <?php elseif (!ParcellaireAffectationConfiguration::getInstance()->isOpen()): ?>
                <div class="panel-body">
                    <?php if(date('Y-m-d') > ParcellaireAffectationConfiguration::getInstance()->getDateOuvertureFin()): ?>
                    <p class="explications">Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'ODG.</p>
                    <?php else: ?>
                    <p class="explications">Le Téléservice sera ouvert à partir du <?php echo format_date(ParcellaireAffectationConfiguration::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
                    <?php endif; ?>
                    <div class="actions">
                        <?php if ($sf_user->isAdmin()): ?>
                                <a class="btn btn-default btn-block" href="<?php echo url_for('parcellaireaffectation_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif(!ParcellaireConfiguration::getInstance()->affectationNeedsIntention() || $intentionParcellaireAffectation):  ?>
            <div class="panel-body">
                <p class="explications">Identifier ou mettre à jour l'affectation de vos<br />parcelles<br /></p>
            	<div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('parcellaireaffectation_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                </div>
            </div>
            <?php else: ?>
                <div class="panel-body">
                    <p>Le Téléservice est fermé car des données sont manquantes. Veuillez contacter directement l'ODG.</p>
                    <div style="margin-top: 77px;">&nbsp;</div>
                </div>
            <?php endif; ?>
            <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
                <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'parcellaireaffectation')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
            </div>
    </div>
</div>
