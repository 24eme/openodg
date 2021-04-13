<?php use_helper('Date'); ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel  <?php if ($parcellaireIrrigable && $parcellaireIrrigable->validation): ?>panel-success<?php else : ?>panel-primary<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Identification&nbsp;des&nbsp;parcelles&nbsp;irrigables</h3>
        </div>
        <?php if ($parcellaireIrrigable && $parcellaireIrrigable->validation): ?>
        <div class="panel-body">
            <p class="explications">Vous avez déjà validé votre Identification des parcelles irrigables.</p>
            <div class="actions">
                <a class="btn btn-block btn-default" href="<?php echo url_for('parcellaireirrigable_visualisation', $parcellaireIrrigable) ?>">Visualiser</a>
           		<?php if($sf_user->isAdmin()): ?>
                <a onclick='return confirm("Êtes vous sûr de vouloir dévalider cette déclaration ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('parcellaireirrigable_devalidation', $parcellaireIrrigable) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
            	<?php endif; ?>
            </div>
        </div>
        <?php elseif ($parcellaireIrrigable):  ?>
            <div class="panel-body">
                <p class="explications">Vous avez débuté votre Identification des parcelles irrigables sans la valider.</p>
                <div class="actions">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('parcellaireirrigable_edit', $parcellaireIrrigable) ?>"><?php if($parcellaireIrrigable->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Continuer la saisie papier<?php else: ?>Continuer la télédéclaration<?php endif; ?></a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-default btn-block" href="<?php echo url_for('parcellaireirrigable_delete', $parcellaireIrrigable) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
          <?php elseif (!ParcellaireIrrigableClient::getInstance()->isOpen()): ?>
                <div class="panel-body">
                    <?php if(date('Y-m-d') > ParcellaireIrrigableClient::getInstance()->getDateOuvertureFin()): ?>
                    <p class="explications">Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'ODG.</p>
                    <?php else: ?>
                    <p class="explications">Le Téléservice sera ouvert à partir du <?php echo format_date(ParcellaireIrrigableClient::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
                    <?php endif; ?>
                    <div class="actions">
                        <?php if ($sf_user->isAdmin()): ?>
                                <a class="btn btn-default btn-block" href="<?php echo url_for('parcellaireirrigable_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                                <a class="btn btn-xs btn-default btn-block" href="<?php echo url_for('parcellaireirrigable_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else:  ?>
            <div class="panel-body">
                <p class="explications">Identifier ou mettre à jour vos parcelles<br />irrigables.</p>
            	<div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('parcellaireirrigable_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                    <?php if ($sf_user->isAdmin()): ?>
                    <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('parcellaireirrigable_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
    </div>
</div>
