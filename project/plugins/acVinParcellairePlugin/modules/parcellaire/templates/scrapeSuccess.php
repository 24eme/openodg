<div class="page-header">
    <h2>Récupération des parcelles de l'établissement <?= $etablissement->identifiant?></h2>
    <img class="img-responsive center-block" src="/images/douane2<?= sfConfig::get('sf_app') ?>.gif" alt="Chargement en cours..." />
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
