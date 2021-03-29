<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>

<?php include_partial('drev/step', array('step' => 'dr_douane', 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération des données de la <?php echo $drev->getDocumentDouanierTypeLibelle() ?></h2>
    <?php if (!$drev->hasDocumentDouanier()): ?>
    <p class="text-center" style="margin-top: 20px;">Traitement des données Prodouane en cours</p>
    <p class="text-center"><span class="img-responsive center-block">
    <?php
        $img_path = dirname(__FILE__).'/../../../../web/images/';
        $douane2app = 'douane2'.sfConfig::get('sf_app');
        if (file_exists($img_path.$douane2app.'.png')): ?>
        <img src="/images/<?= $douane2app ?>.png" alt="Chargement en cours..." />
    <?php else: ?>
        <img src="/images/douane2.gif" alt="Chargement en cours..." /><img width="150" src="/images/<?= $douane2app ?>.png"/>
    <?php endif; ?>
    </span></p>

    <form action="<?php echo url_for('drev_scrape_dr', $drev); ?>" method="get" id="form">
    	<div style="margin-top: 20px;" class="row row-margin row-button">
        	<div class="col-xs-6">
            	<a href="<?php echo url_for('drev_exploitation', $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        	</div>
        	<div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-primary btn-upper" <?php if (!$drev->hasDocumentDouanier()): ?>disabled="disabled"<?php endif; ?>>Valider et continuer  <span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
		</div>
    </form>
    <?php else: ?>
        <p class="text-center" style="margin-top: 20px; padding: 150px;">Les données de la <?php echo $drev->getDocumentDouanierTypeLibelle() ?> ont correctement été importées <?php if($sf_user->isAdmin()): ?><small>(<a href="<?php echo url_for('drev_dr_upload', array('sf_subject' => $drev, 'force' => true)) ?>">changer le fichier</a>)</small><?php endif; ?>. </p>
        <div style="margin-top: 20px;" class="row row-margin row-button">
        	<div class="col-xs-6">
            	<a href="<?php echo url_for('drev_exploitation', $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        	</div>
        	<div class="col-xs-6 text-right">
                <a href="<?php echo url_for('drev_revendication_superficie', $drev) ?>" class="btn btn-primary btn-upper">Valider et continuer  <span class="glyphicon glyphicon-chevron-right"></span></a>
            </div>
		</div>
    <?php endif; ?>
</div>
<?php if (!$drev->hasDocumentDouanier()): ?>
<script type="text/javascript">
	setTimeout(function(){document.getElementById("form").submit();}, 500);
</script>
<?php endif; ?>
