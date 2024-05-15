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
            <th></th>
            <th>Produit</th>
            <th>Déclaré</th>
            <th>Recu des apporteurs</th>
        </tr>
    </thead>
    <?php if (isset($tableau_comparaison)): ?>
        <tbody>
            <?php foreach ($tableau_comparaison as $produit => $raison_sociale): ?>
                <tr>
                    <div class="row">
                        <td class="col-xs-1"><button type="button" class="center-block glyphicon glyphicon-eye-open" data-toggle="collapse" data-target="#collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>" aria-expanded="false" aria-controls="collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>"></button></td>
                        <td class="col-xs-5"><?php echo $produit; ?></td>
                        <?php $recu = 0; foreach($raison_sociale as $raison_sociale => $declare): ?>
                            <?php if ($raison_sociale == $dr->getEtablissementObject()->raison_sociale): ?>
                                <td class="col-xs-3 text-right"><?php echo $declare ?></td>
                            <?php else: ?>
                                <?php $recu += $declare ?>
                            <?php endif;?>
                        <?php endforeach; ?>
                        <td class="col-xs-3 text-right"><?php echo $recu; ?></td>
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
            <tr><td colspan=3><center><i>Pas de données des apporteurs</i></center></td></tr>
        </tbody>
    <?php endif; ?>
</table>
