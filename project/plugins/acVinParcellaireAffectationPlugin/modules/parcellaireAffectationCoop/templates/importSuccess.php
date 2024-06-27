<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border">
    <h2>Import des affectations parcellaires</h2>
</div>

<div class="alert alert-danger">
Les erreurs suivantes ont été détéctées dans le fichier d'import :<br /><br />
<ul>
<?php foreach($erreursRecap as $message => $numLignes): ?>
    <li><?php echo $message ?> : <?php foreach($numLignes as $numLigne): ?><a href="#<?php echo $numLigne ?>">#<?php echo $numLigne ?></a> <?php endforeach; ?></li>
<?php endforeach; ?>
</ul>
</div>

<h3>Données à importer</h3>

<div class="table-responsive">
<table class="table table-bordered table-striped table-condensed small" style="font-family: monospace;">
    <tr>
        <th>N° Ligne</th>
        <th>Classification</th>
        <th>Structure d'apport</th>
        <th>CVI</th>
        <th>Raison sociale</th>
        <th>Commune</th>
        <th>Lieu-dit</th>
        <th>Faire valoir</th>
        <th>Section cadastrale</th>
        <th>Numéro cadastral</th>
        <th>Superficie (ha)</th>
        <th>Année de plantation</th>
        <th>Cépage</th>
        <th>Densité de plantation</th>
        <th>Ecartement sur le rang (m)</th>
        <th>Ecartement entre rangs (m)</th>
        <th>% de manquants si supérieur à 20%</th>
        <th>Parcelle pouvant recourir à l'irrigation OUI/NON</th>
        <th>Système d'irrigation fixe OUI/NON</th>
        <th>Type de ressource</th>
        <th>Parcelle irriguée
    </tr>
<?php foreach($parcellaireImport->getCsv() as $numLigne => $data): ?>
    <tr class="<?php if(isset($erreurs[$numLigne])): ?>danger text-danger<?php endif; ?>">
        <td><a name="<?php echo $numLigne ?>">#<?php echo $numLigne ?></a></td>
        <?php foreach($data as $key => $value): ?>
            <td><?php echo $value ?></td>
        <?php endforeach; ?>
    </tr>
    <?php if(isset($erreurs[$numLigne])): ?>
    <tr class="<?php if(isset($erreurs[$numLigne])): ?>danger text-danger<?php endif; ?>">
        <td></td>
        <th>Erreur</th>
        <th colspan="20"><?php echo $erreurs[$numLigne]['message'] ?></th>
    </tr>
    <?php endif; ?>
<?php endforeach; ?>
</table>
</div>

<a href="" class="btn btn-primary pull-right" disabled>Importer les données</a>
