<?php if(strpos($etablissement->famille, 'PRODUCTEUR') === false): return; endif; ?>

<?php use_helper('Date'); ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel  <?php if ($parcellaireManquant && $parcellaireManquant->validation): ?>panel-success<?php elseif($parcellaireManquant) : ?>panel-primary<?php else : ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Déclaration&nbsp;de&nbsp;pieds&nbsp;manquants&nbsp;<?php echo $periode ?></h3>
        </div>
        <?php if (!$parcellaireManquant && $needAffectation): ?>
        <div class="panel-body">
            <p class="explications">Cette déclaration s'appuie sur l'affectation parcellaire qui n'a pas encore été saisie et approuvée pour la période <?php echo $periode ?>.</p>
        </div>
        <?php elseif (!$parcellaireManquant && !$parcellaire): ?>
        <div class="panel-body">
            <p class="explications">Les données de votre parcellaire ne sont pas présente sur la plateforme.<br/><br/>Il ne vous est donc pas possible de déclarer vos pieds morts ou manquants : <a href="<?php echo url_for("parcellaire_declarant", $etablissement) ?>">Voir le parcellaire</a></p>
        </div>
        <?php elseif ($parcellaireManquant && $parcellaireManquant->validation): ?>
        <div class="panel-body">
            <p class="explications">Vous avez déjà validé votre déclaration de pieds manquants</p>
            <div class="actions">
                <a class="btn btn-block btn-default" href="<?php echo url_for('parcellairemanquant_visualisation', $parcellaireManquant) ?>">Visualiser</a>
           		<?php if($sf_user->isAdmin()): ?>
                <a onclick='return confirm("Êtes vous sûr de vouloir dévalider cette déclaration ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('parcellairemanquant_devalidation', $parcellaireManquant) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
            	<?php endif; ?>
            </div>
        </div>
        <?php elseif ($parcellaireManquant):  ?>
            <div class="panel-body">
                <p class="explications">Vous avez débuté votre déclaration de pieds manquants sans la valider.</p>
                <div class="actions">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('parcellairemanquant_edit', $parcellaireManquant) ?>"><?php if($parcellaireManquant->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer la saisie papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-default btn-block" href="<?php echo url_for('parcellairemanquant_delete', $parcellaireManquant) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
          <?php elseif (!ParcellaireManquantConfiguration::getInstance()->isOpen()): ?>
                <div class="panel-body">
                    <?php if(date('Y-m-d') > ParcellaireManquantConfiguration::getInstance()->getDateOuvertureFin()): ?>
                    <p class="explications">Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'ODG.</p>
                    <?php else: ?>
                    <p class="explications">Le Téléservice sera ouvert à partir du <?php echo format_date(ParcellaireManquantConfiguration::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
                    <?php endif; ?>
                    <div class="actions">
                        <?php if ($sf_user->isAdminODG()): ?>
                                <a class="btn btn-default btn-block" href="<?php echo url_for('parcellairemanquant_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                                <a class="btn btn-xs btn-default btn-block" href="<?php echo url_for('parcellairemanquant_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else:  ?>
            <div class="panel-body">
                <p class="explications">Déclarer vos parcelles de pieds morts ou manquants.</p>
            	<div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('parcellairemanquant_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                    <?php if ($sf_user->isAdminODG()): ?>
                    <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('parcellairemanquant_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
                <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'parcellaireManquant')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
            </div>
    </div>
</div>
