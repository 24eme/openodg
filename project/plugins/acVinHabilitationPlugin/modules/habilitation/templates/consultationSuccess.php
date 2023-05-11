<?php use_helper('Compte'); ?>

<ol class="breadcrumb">
    <li class="active"><a href=""><span class="glyphicon glyphicon-search"></span> Consultation des habilitations</a></li>
</ol>

<h3>Consulter l'habilitation d'un opérateur</h3>

<form method="get" action="" role="form" class="form-horizontal" style="margin-top: 30px;">
    <div class="form-group">
        <div class="col-sm-4 col-sm-offset-3 col-xs-12">
            <input name="numero" type="text" class="form-control" placeholder="Numéro CVI ou SIRET" autofocus="autofocus" <?php if(isset($numero)): ?>value="<?php echo $numero ?>"<?php endif ?>/>
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
    <a class="btn btn-link pull-right btn-sm" onclick="navigator.clipboard.writeText(this.href); alert('Le lien a été copié dans le presse papier !'); return false;" title="Copier le lien vers cette page" href="<?php echo url_for('habilitation_consultation', ['numero' => $numero]) ?>"><small class="glyphicon glyphicon-link"></small> Lien vers cette page</a>
    <a class="btn btn-link pull-right btn-sm" title="Export JSON" href="<?php echo url_for('habilitation_consultation', ['numero' => $numero, 'format' => 'json']) ?>"><small class="glyphicon glyphicon-transfer"></small> JSON</a>
    <?php if($sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION)): ?>
    <a class="btn-link btn-sm pull-right" href="<?php echo url_for('habilitation_declarant', $habilitation->getEtablissementObject()); ?>"><small class="glyphicon glyphicon-eye-open"></small> Voir la version complète</a>
    <?php endif; ?>
    <h4><span class="glyphicon glyphicon-home"></span> CVI : <?php echo $etablissement->getCvi(); ?>
    <?php if($etablissement->getSiret()): ?> - SIREN : <?php echo formatSIRET($etablissement->getSiret(), true); ?><?php endif; ?></h4>
</div>

<?php include_partial('habilitation/habilitation', array('habilitation' => $habilitation, 'public' => true)); ?>
