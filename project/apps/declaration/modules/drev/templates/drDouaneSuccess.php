<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>

<?php include_partial('drev/step', array('step' => 'dr_douane', 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération des données de la Déclaration de Récolte</h2>
</div>
<form method="post">
<p>Vous allez être redirigé sur la plateforme du CIVA afin de récupérer les données de votre Déclaration de Récolte.</p>
<div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
    <div class="col-xs-6 text-right">
        <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
            <button id="btn-validation" type="submit" class="btn btn-primary btn-upper">Retourner à la validation <span class="glyphicon glyphicon-check"></span></button>
        <?php else: ?>
            <button type="submit" class="btn btn-primary btn-upper">Continuer vers la revendication <span class="glyphicon glyphicon-chevron-right"></span></button>
        <?php endif; ?>
    </div>
</div>
</form>
