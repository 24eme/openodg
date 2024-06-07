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

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Produit</th>
            <th>Volumes issus de la SV</th>
            <th>Volumes issus de la DR</th>
            <th>Différence</th>
        </tr>
    </thead>
    <?php if (isset($tableau_comparaison)): ?>
        <?php foreach ($tableau_comparaison as $produit => $cvis): ?>
            <?php $totalDeclarantSV = $cvis[$dr->getEtablissementObject()->getCvi()]['SV']; $totalApporteurDR = $cvis[$dr->getEtablissementObject()->getCvi()]['DR']; $diffSVDR = round($totalDeclarantSV - $totalApporteurDR, 2); ?>
            <tbody>
                <tr>
                    <div class="row">
                        <td class="col-xs-5"><?php echo $produit; ?></td>
                        <td class="col-xs-2 text-right"><?php echo $totalDeclarantSV ; ?></td>
                        <td class="col-xs-2 text-right"><?php echo $totalApporteurDR ; ?></td>
                        <td class="col-xs-1 text-center strong <?php if (! $diffSVDR) { echo 'bg-success'; } else { echo 'bg-danger'; }; ?>">
                            <?php echo $diffSVDR; ?>
                            <?php if ($diffSVDR): ?>
                                <button type="button" class="center-block glyphicon glyphicon-collapse-down ml-4" data-toggle="collapse" data-target="#collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>" aria-expanded="false" aria-controls="collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>" id="collapseButton"></button>
                            <?php endif; ?>
                        </td>
                    </div>
                </tr>
            </tbody>
            <tbody class="collapse" id="collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>">
                <?php foreach($cvis as $cvi => $valeur): ?>
                    <tr>
                        <?php if ($cvi == $dr->getEtablissementObject()->cvi) { continue; } ?>
                        <?php if (round($valeur['DR'] - $valeur['SV'], 2) == 0) { continue; } ?>
                        <td class="col-xs-2 text-right">
                            <?php $etablissement = EtablissementClient::getInstance()->findByCvi($cvi); ?>
                            <a href="<?php echo url_for('dr_visualisation', ['id' =>'DR-'.$etablissement->identifiant.'-'.$dr->campagne]); ?>"><?php echo $etablissement->getNom(); ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a>
                        </td>
                        <td class="text-right">
                            <?php echo $valeur['SV']; ?>
                        </td>
                        <td class="text-right">
                            <?php echo $valeur['DR']; ?>
                        </td>
                        <td class="text-center">
                            <?php echo round($valeur['SV'] - $valeur['DR'], 2); ?>
                            <button type="button" class="center-block glyphicon glyphicon-collapse-down ml-4" style="visibility: hidden;"></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        <?php endforeach; ?>
    <?php else: ?>
        <tbody>
            <tr><td colspan=5><center><i>Pas de données des apporteurs</i></center></td></tr>
        </tbody>
    <?php endif; ?>
</table>
