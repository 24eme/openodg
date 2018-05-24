<?php use_helper('Date'); ?>
<?php if ($parcellaireIrrigable && $parcellaireIrrigable->validation): ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Identification&nbsp;des&nbsp;parcelles&nbsp;irriguées</h3>
        </div>
        <div class="panel-body">
            <p><?php if(!$parcellaireIrrigue): ?>Identifier<?php else: ?>Mettre à jour<?php endif; ?> vos parcelles irriguées.<br />&nbsp;</p>
          	<div style="margin-top: 50px;">
                <a class="btn btn-block btn-default" href="<?php echo url_for('parcellaireirrigue_edit', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'papier' => false)) ?>"><?php if(!$parcellaireIrrigue): ?>Démarrer<?php else: ?>Continuer<?php endif; ?> la télédéclaration</a>
                <?php if ($sf_user->isAdmin()): ?>
                <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('parcellaireirrigue_edit', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'papier' => true)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;<?php if(!$parcellaireIrrigue): ?>Saisir<?php else: ?>Poursuivre<?php endif; ?> la déclaration papier</a>
                <?php endif; ?>
            </div>
        </div>
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
			<div style="margin-top: 50px;"></div>
		</div>
    </div>
</div>
<?php endif; ?>
