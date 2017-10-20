<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>

<?php include_partial('drev/step', array('step' => 'dr_douane', 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération des données de la Déclaration de Récolte</h2>
    <?php if (!$drev->hasDR()): ?>
    <p class="text-center" style="margin-top: 20px;">Traitement des données Prodouane en cours</p>
    <p class="text-center"><img height="40" src="/images/loader.gif" alt="chargement en cours..." /></p>
    <?php else: ?>
    <p class="text-center" style="margin-top: 20px;">Les données de la DR ont correctement été importées.</p>
    <?php endif; ?>
    <form action="<?php echo url_for('drev_dr', $drev); ?>" method="get" id="form">
    	<div style="margin-top: 20px;" class="row row-margin row-button">
        	<div class="col-xs-6">
            	<a href="<?php echo url_for('drev_exploitation', $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        	</div>
        	<div class="col-xs-6 text-right">
            	<button type="submit" class="btn btn-primary btn-upper" <?php if (!$drev->hasDR()): ?>disabled="disabled"<?php endif; ?>>Valider et continuer  <span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
		</div>
    </form>
</div>
<?php if (!$drev->hasDR()): ?>
<script type="text/javascript">
	setTimeout(function(){document.getElementById("form").submit();}, 500);
</script>
<?php endif; ?>