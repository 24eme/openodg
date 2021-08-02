<div class="page-header">
    <h2>Récupération des parcelles de l'établissement <?= $etablissement->identifiant?></h2>
    <p class="text-center">
    <span class="img-responsive center-block">
    <?php
        $img_path = dirname(__FILE__).'/../../../../web/images/';
        $douane2app = 'douane2'.sfConfig::get('sf_app');
        if (file_exists($img_path.$douane2app.'.gif')): ?>
    <?php else: ?>
        <img src="/images/douane2.gif" alt="Chargement en cours..." style="max-width: 70%"/>
        <img src="/images/<?= $douane2app ?>.png" width="150" style="max-width: 30%"/>
    <?php endif; ?>
    </span></p>
</div>
<form action="<?php echo url_for('parcellaire_import_csv', $etablissement); ?>" method="get" id="form">
    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('parcellaire_declarant', $etablissement) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
    </div>
</form>

<script>
    setTimeout(function(){document.getElementById("form").submit();}, 500);
</script>
