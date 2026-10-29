<?php use_helper('Compte'); ?>

<ol class="breadcrumb">
    <li class="active"><a href=""><span class="glyphicon glyphicon-search"></span> Consultation des habilitations</a></li>
    <?php if($sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION) && isset($habilitation)): ?>
    <li class="pull-right">
        <a title="Consultation des habilitations" style="opacity: 0.25" href="<?php echo url_for('habilitation_declarant', $habilitation->getEtablissementObject()); ?>">Voir la version complète</a></li>
    <?php endif; ?>
</ol>

<h3>Consulter l'habilitation d'un opérateur</h3>

<form method="get" action="" role="form" class="form-horizontal" style="margin-top: 30px;">
    <div class="form-group">
        <div class="col-sm-4 col-sm-offset-3 col-xs-12">
            <input name="numero" type="text" class="form-control" placeholder="Numéro CVI" autofocus="autofocus" <?php if(isset($numero)): ?>value="<?php echo $numero ?>"<?php endif ?>/>
        </div>
        <div class="col-xs-2 hidden-xs">
            <button class="btn btn-default" type="submit">Voir l'habilitation</button>
        </div>
    </div>
</form>

<?php if(!isset($numero) && !isset($habilitation)): return; endif; ?>

<hr style="margin-top: 20px; margin-bottom: 20px;" />

<?php if(isset($numero) && !isset($habilitation)): ?>
<p class="text-center"><em>Aucune habilitation trouvée</em></p>
<?php return; ?>
<?php endif; ?>

<div class="well">
    <a class="btn btn-link pull-right btn-sm" onclick="navigator.clipboard.writeText(this.href); alert('Le lien a été copié dans le presse papier !'); return false;" title="Copier le lien vers cette page" href="<?php echo url_for('habilitation_consultation', ['numero' => $numero]) ?>"><small class="glyphicon glyphicon-link"></small></a>
    <h4><span class="glyphicon glyphicon-home"></span> CVI : <?php echo $etablissement->getCvi(); ?>
    <?php if($etablissement->getSiret()): ?> - SIREN : <?php echo formatSIRET($etablissement->getSiret(), true); ?><?php endif; ?></h4>
</div>

<?php include_partial('habilitation/habilitation', array('habilitation' => $habilitation, 'public' => true)); ?>

<a style="margin-top: -15px;" class="pull-right" title="Export JSON" href="<?php echo url_for('habilitation_consultation', ['numero' => $numero, 'format' => 'json']) ?>"><small>JSON</small></a>
