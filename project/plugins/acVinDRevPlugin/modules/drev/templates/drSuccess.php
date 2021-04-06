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
        <div class="col-xs-6 col-xs-offset-6 text-right">
            <input type="submit" class="btn btn-primary btn-upper" id="submit-dr" value="Lancer la récupération" />
        </div>
    </form>
    <?php else: ?>
        <p class="text-center" style="margin-top: 20px; padding: 150px;">Les données de la <?php echo $drev->getDocumentDouanierTypeLibelle() ?> ont correctement été importées <?php if($sf_user->isAdmin()): ?><small>(<a href="<?php echo url_for('drev_dr_upload', array('sf_subject' => $drev, 'force' => true)) ?>">changer le fichier</a>)</small><?php endif; ?>. </p>
        <div class="col-xs-offset-6 col-xs-6 text-right">
            <a href="<?php echo url_for('drev_revendication_superficie', $drev) ?>" class="btn btn-primary btn-upper">Valider et continuer  <span class="glyphicon glyphicon-chevron-right"></span></a>
        </div>
    <?php endif; ?>
</div>
<?php if (!$drev->hasDocumentDouanier()): ?>
<script type="text/javascript">
	setTimeout(function(){document.getElementById("form").submit();document.getElementById("submit-dr").setAttribute('disabled', 'disabled');document.getElementById("submit-dr").setAttribute('value', 'Import en cours ;)');}, 500);
</script>
<?php endif; ?>
