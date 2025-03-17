<?php use_helper('Date'); ?>

<?php include_partial('global/flash'); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $sv->identifiant, 'campagne' => $sv->campagne)); ?>"><?php echo $sv->getEtablissementObject()->getNom() ?> (<?php echo $sv->getEtablissementObject()->identifiant ?> - <?php echo $sv->getEtablissementObject()->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('dr_visualisation', array('id' => $sv->_id)); ?>"><?php if($sv->isBailleur()) echo "Synthèse bailleur "; else echo $sv->type; ?> de <?php echo $sv->getperiode(); ?></a></li>
  <li class="active"><a href="">Vérification</a></li>
</ol>

<div class="page-header no-border">
    <h2>Tableau de vérification <?php echo $sv->type; ?> <?= $sv->campagne ?></h2>
</div>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $sv->getEtablissementObject()]); ?>
</div>

<?php use_helper('Float') ?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Produit</th>
            <th>Volumes issus de la SV</th>
            <th>Volumes issus de la DR</th>
            <th>Différence</th>
            <th>Détails</th>
        </tr>
    </thead>
    <?php if ($tableau_comparaison = $sv->getTableauComparaisonTiersApporteurs()) : ?>
        <?php foreach ($tableau_comparaison as $produit => $cvis): ?>
            <?php $totalDeclarantSV = $cvis[$sv->getEtablissementObject()->getCvi()]['SV']; $totalApporteurDR = $cvis[$sv->getEtablissementObject()->getCvi()]['DR']; $diffSVDR = round($totalDeclarantSV - $totalApporteurDR, 2); ?>
            <tbody>
                <tr>
                    <div class="row">
                        <td class="col-xs-4"><?php echo explode('|', $produit)[1]; ?></td>
                        <td class="col-xs-3 text-right"><?php echo abs($totalDeclarantSV) ; ?></td>
                        <td class="col-xs-3 text-right"><?php echo abs($totalApporteurDR) ; ?></td>
                        <td class="col-xs-1 text-center strong <?php if (! $diffSVDR) { echo 'bg-success'; } else { echo 'bg-danger'; }; ?>">
                            <?php echo abs($diffSVDR); ?>
                        </td>
                        <td class="col-xs-1 text-center">
                            <?php if ($diffSVDR): ?>
                                <button type="button" class="glyphicon glyphicon-collapse-down" data-toggle="collapse" data-target="#collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>" aria-expanded="false" aria-controls="collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>" id="collapseButton"></button>
                            <?php endif; ?>
                        </td>
                    </div>
                </tr>
            </tbody>
            <tbody class="collapse" id="collapsibleRow_<?php echo KeyInflector::slugify($produit); ?>">
                <?php foreach($cvis as $cvi => $valeur): ?>
                    <?php if ($cvi == $sv->getEtablissementObject()->cvi) { continue; } ?>
                    <?php if (round($valeur['DR'] - $valeur['SV'], 2) == 0) { continue; } ?>
                    <tr>
                        <td class="text-right">
                            <?php $etablissement = $sv->getCachedTiersByCVI($cvi); ?>
                            <?php if ($etablissement): ?>
                                <?php $dr_apporteur = DRClient::getInstance()->find('DR-'.$etablissement->identifiant.'-'.$sv->campagne); ?>
                                <?php if ($dr_apporteur): ?>
                                    <a href="<?php echo url_for('dr_visualisation', ['id' =>'DR-'.$etablissement->identifiant.'-'.$sv->campagne]); ?>"><?php echo $etablissement->getNom(); ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a>
                                <?php else: ?>
                                    <a href="<?php echo url_for('declaration_etablissement', ['identifiant' => $etablissement->identifiant]); ?>"><?php echo $etablissement->getNom(); ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php echo $cvi; ?>
                                <small> - CVI non reconnu</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php echo abs($valeur['SV']); ?>
                        </td>
                        <td class="text-right">
                            <?php if (isset($dr_apporteur) && $dr_apporteur): ?>
                                <?php echo abs($valeur['DR']); ?>
                            <?php else: ?>
                                <small>DR absente</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php echo abs(round($valeur['SV'] - $valeur['DR'], 2)); ?>
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
<h2>Vérification des habilitations des tiers</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Etablissement</th>
            <th>Habilitation Producteur (<a href="#" onclick="$('.habilitation').show(); return false;">toutes</a>)</th>
        </tr>
    </thead>
    <tbody>
<?php $operateur_nb = 0 ; $habilitation_ok = 0 ; $etablissement_ok = 0; ?>
<?php foreach ($sv->getHabilitationTiers() as $cvi => $hab): $e = $sv->getCachedTiersByCVI($cvi); $operateur_nb++;?>
    <tr class="habilitation <?php echo ($hab['habilitation_ok']) ? "collapse" : ""; ?>">
        <td>
            <?php if ($e) : $etablissement_ok++ ; ?>
            <a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $e->identifiant));?>"><?php echo $e->raison_sociale.' - '.$cvi; ?></a>
            <?php else: ?>
            <?php echo 'inconnu - '.$cvi; ?>
            <?php endif; ?>
        </td>
        <td class="<?php echo (isset($hab['habilitation_ok'])  && $hab['habilitation_ok']) ? 'bg-success': 'bg-danger'; ?>">
            <?php if($hab['habilitation_ok']): $habilitation_ok++ ; ?>
                <a href="<?php echo url_for('habilitation_visualisation', $hab['habilitation']); ?>">Habilitation OK</a>
            <?php else: ?>
                <?php if (isset($hab['habilitation'])): ?>
                <a href="<?php echo url_for('habilitation_visualisation', $hab['habilitation']); ?>">Habilitation ERREUR</a>
                <?php else: ?>
                Habilitation inexistante
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
<tr>
    <td class="text-center"><?php echo $etablissement_ok; ?> établissements trouvés <span class="text-muted">/ <?php echo $operateur_nb; ?></span></td>
    <td class="text-center"><?php echo $habilitation_ok; ?> habilitations correctes <span class="text-muted">/ <?php echo $operateur_nb; ?></span></td>
</tr>
</table>
<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?= (isset($service) && $service) ?: url_for('dr_visualisation', array('id' => $sv->_id)); ?>"
            class="btn btn-default"
            >
            <i class="glyphicon glyphicon-chevron-left"></i> Retour
        </a>
    </div>
</div>
