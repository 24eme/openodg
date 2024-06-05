<?php use_helper('Date'); ?>

<?php include_partial('dr/breadcrumb', array('dr' => $dr )); ?>
<?php include_partial('global/flash'); ?>

<div class="page-header no-border clearfix">
    <h2>Tableau de vérification <?php echo $dr->type; ?> <?= $dr->campagne ?></h2>
</div>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $dr->getEtablissementObject()]); ?>
</div>

<?php use_helper('Float') ?>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Produit</th>
            <th>Déclaré</th>
            <th>Différence</th>
            <th>Recu des apporteurs</th>
            <th>Détails</th>
        </tr>
    </thead>
    <?php if (isset($tableau_comparaison)): ?>
        <tbody>
            <?php foreach ($tableau_comparaison as $produit => $raison_sociale): ?>
                <tr>
                    <div class="row">
                        <td class="col-xs-5"><?php echo $produit; ?></td>
                        <?php $recu = 0; foreach($raison_sociale as $raison_sociale => $declare): ?>
                            <?php if ($raison_sociale == $dr->getEtablissementObject()->raison_sociale): ?>
                                <td class="col-xs-2 text-right"><?php $declareSV = $declare ;echo $declareSV ?></td>
                            <?php else: ?>
                                <?php $recu += $declare ?>
                            <?php endif;?>
                        <?php endforeach; ?>
                        <td class="col-xs-2 text-right"><?php echo round($declareSV - $recu, 2); ?></td>
                        <td class="col-xs-2 text-right"><?php echo $recu; ?></td>
                        <td class="col-xs-1"><button type="button" class="center-block glyphicon glyphicon-collapse-down" data-toggle="collapse" data-target="#collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>" aria-expanded="false" aria-controls="collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>" id="collapseButton"></button></td>
                    </div>
                </tr>
                <tr>
                    <td colspan="3" style="padding: 0; border: none;">
                        <div class="collapse" id="collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>">
                            Test
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    <?php else: ?>
        <tbody>
            <tr><td colspan=5><center><i>Pas de données des apporteurs</i></center></td></tr>
        </tbody>
    <?php endif; ?>
</table>
