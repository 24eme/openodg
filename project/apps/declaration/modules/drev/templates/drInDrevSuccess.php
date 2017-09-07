<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>

<?php include_partial('drev/step', array('step' => 'dr_douane', 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération des données de la Déclaration de Récolte</h2>
    <p class="text-center" style="margin-top: 20px;">Traitement des données Prodouane en cours</p>
    <p class="text-center"><img height="40" src="/images/loader.gif" alt="chargement en cours..." /></p>
</div>
<script type="text/javascript">
	setTimeout(function(){document.location = "<?php echo url_for('drev_revendication', $drev) ?>";}, 3000);
</script>