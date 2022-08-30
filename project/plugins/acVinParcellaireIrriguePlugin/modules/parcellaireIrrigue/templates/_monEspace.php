<?php use_helper('Date'); ?>
<?php if ($parcellaireIrrigable && $parcellaireIrrigable->validation): ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Identification&nbsp;des&nbsp;parcelles&nbsp;irriguées</h3>
        </div>
    <?php if (!ParcellaireIrrigueClient::getInstance()->isOpen()): ?>
          <div class="panel-body">
              <?php if(date('Y-m-d') > ParcellaireIrrigueClient::getInstance()->getDateOuvertureFin()): ?>
              <p class="explications">Le Téléservice est fermé. Pour toute question, veuillez contacter directement l'ODG.</p>
              <?php else: ?>
              <p class="explications">Le Téléservice sera ouvert à partir du <?php echo format_date(ParcellaireIrrigueClient::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
              <?php endif; ?>
              <div class="actions">
                  <?php if ($sf_user->isAdmin()): ?>
                          <a class="btn btn-default btn-block" href="<?php echo url_for('parcellaireirrigue_edit', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>">Démarrer la télédéclaration</a>
                          <a class="btn btn-xs btn-default btn-block" href="<?php echo url_for('parcellaireirrigue_edit', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'papier' => true)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
                  <?php endif; ?>
              </div>
          </div>
      <?php else:  ?>
        <div class="panel-body">
            <p class="explications"><?php if(!$parcellaireIrrigue): ?>Identifier<?php else: ?>Mettre à jour<?php endif; ?> vos parcelles irriguées.<br />&nbsp;</p>
          	<div class="actions">
                <a class="btn btn-block btn-default" href="<?php echo url_for('parcellaireirrigue_edit', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'papier' => false)) ?>"><?php if(!$parcellaireIrrigue): ?>Démarrer la télédéclaration<?php else: ?>Visualiser et continuer à déclarer<?php endif; ?></a>
                <?php if ($sf_user->isAdmin()): ?>
                <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('parcellaireirrigue_edit', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'papier' => true)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;<?php if(!$parcellaireIrrigue): ?>Saisir<?php else: ?>Poursuivre<?php endif; ?> la déclaration papier</a>
                <?php endif; ?>
            </div>
        </div>
      <?php endif; ?>
    </div>
</div>
<?php elseif ($parcellaireIrrigable && !$parcellaireIrrigable->validation): ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Identification&nbsp;des&nbsp;parcelles&nbsp;irriguées</h3>
        </div>
		<div class="panel-body">
			<p>Vous devez valider votre identification des parcelles irrigables pour pouvoir identifier vos parcelles irriguées.</p>
			<div style="margin-top: 97px;"></div>
		</div>
    </div>
</div>
<?php endif; ?>
