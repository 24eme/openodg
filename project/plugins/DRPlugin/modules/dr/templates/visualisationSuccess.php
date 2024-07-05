<?php use_helper('Date'); ?>

<?php include_partial('dr/breadcrumb', array('dr' => $dr )); ?>
<?php include_partial('global/flash'); ?>

<div class="page-header no-border clearfix">
    <h2>
        Déclaration <?php if ($dr->type == 'DR') echo 'de Récolte'; else echo $dr->type; ?> <?= $dr->campagne ?>
        <small class="pull-right">
            <i class="glyphicon glyphicon-file"></i>
            Déclaration
            <?php if ($dr->exist('statut_odg') && $dr->statut_odg): ?>
                mise en attente,
            <?php endif ?>
            importée le <?= format_date($dr->date_import, "dd/MM/yyyy", "fr_FR") ?>
            <?php if ($dr->exist('validation_odg') && $dr->validation_odg): ?>
                et approuvée le <?= format_date($dr->validation_odg, "dd/MM/yyyy", "fr_FR") ?>
            <?php endif ?>
        </small>
    </h2>
</div>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $dr->getEtablissementObject()]); ?>
</div>

<?php if (isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('dr/pointsAttentions', ['validation' => $validation, 'noLink' => true]); ?>
<?php endif ?>

<?php use_helper('Float') ?>

<h3>Détail par produit</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-5" style="border-top: hidden; border-left: hidden"></th>
            <th colspan="11" class="text-center">Lignes</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th class="text-center col-xs-5 clearfix">Produits
<?php if ($dr->getDocumentDefinitionModel() == 'DR'): ?>
                <small class="pull-right text-muted">Rdmt L5|L15</small>
<?php else: ?>
                <small class="pull-right text-muted">Rdmt L15</small>
<?php endif; ?>
            </th>
            <?php $produits = $dr->getProduitsDetail(); ?>
            <?php foreach ($produits['lignes']->getRawValue() as $libelle): ?>
                <th class="text-center" style="cursor: help" title="<?= DouaneCsvFile::getCategorieLibelle($dr->type, $libelle) ?>">L<?= $libelle ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produits['produits']->getRawValue() as $hash => $produit): ?>
            <tr>
                <td>
                    <?= $produit['libelle'] ?>
                    <br />
                    <small class="pull-right text-muted">
<?php if ($dr->getDocumentDefinitionModel() == 'DR'): ?>
                        <span title="Rendement L5" style="cursor: help">
                            <!-- Calcul rendement L5 : Si quelqu'un demande a soustraire la L16 a la L5, il faut faire un point pour trouver le besoin. Derniere modif faite pour igp var -->
                            <?php if ($produit['lignes']['05']['val'] > 0 && $produit['lignes']['04']['val'] > 0): ?>
                                <?php echoFloatFr(round( ($produit['lignes']['05']['val'] * 1) / $produit['lignes']['04']['val'], 2)); ?>
                            <?php else: echoFloatFr(0) ?>
                            <?php endif ?>
                        </span> hl/ha
                        |
<?php endif ?>
                        <span title="Rendement L15" style="cursor: help">
                            <?php if ($produit['lignes']['15']['val'] > 0 && $produit['lignes']['04']['val'] > 0): ?>
                                <?php echoFloatFr( round( ($produit['lignes']['15']['val']) / $produit['lignes']['04']['val'], 2) ) ;?>
                            <?php else: echoFloatFr(0); ?>
                            <?php endif ?>
                        </span> hl/ha
                    </small>
                </td>
                <?php foreach ($produit['lignes'] as $l => $p): ?>
                <td class="text-right" title="Ligne L<?= $l ?>">
                  <?= ($p['val'] === '—') ? '—' : echoFloat($p['val']) ?> <span class="text-muted"><?= $p['unit'] ?? '' ?></span>
                </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        <tr>
            <th class="text-right"><strong>Total</strong></th>
            <?php foreach ($produit['lignes'] as $l => $p): ?>
                <th class="text-right"><strong><?= echoFloat($dr->getTotalValeur($l)) ?></strong>&nbsp;<span class='text-muted'><?= $p['unit'] ?></span></th>
            <?php endforeach ?>
        </tr>
    </tbody>
</table>
<?php $bailleurs = $dr->getBailleurs()->getRawValue(); ?>
<?php if(count($bailleurs)): ?>
    <p style="margin-top: -10px; margin-bottom: 20px;">
    Une partie des volumes ont été récoltés pour le compte <?php if(count($bailleurs) > 1): ?>des<?php else: ?>du<?php endif; ?> bailleur<?php if(count($bailleurs) > 1): ?>s :<?php endif; ?>
     <?php foreach($bailleurs as $b): ?>
        <?php  if (!$b['etablissement_id']): continue; endif; ?>
        <a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $b['etablissement_id'], 'campagne' => $dr->campagne)) ?>"><?php echo $b['raison_sociale']; ?></a>
    <?php endforeach; ?>. Ces volumes ne figurent pas dans le tableau.
    </p>
<?php endif; ?>

<?php
$tiers = array();
if ($dr->isApporteur()):
    $tiers = $dr->getTiers()->getRawValue();
    $tiers_type = 'tiers (négociants et coopératives)';
elseif ($dr->hasApporteurs(true)):
    $tiers = $dr->getApporteurs(true)->getRawValue();
    $tiers_type = 'apporteurs';
endif;
?>
<?php if(count($tiers)): ?>
    <p style="margin-top: -10px; margin-bottom: 20px;">
        Ce document implique <?php echo count($tiers); ?> <?php echo $tiers_type; ?> :
<?php
    $list = array();
    foreach($tiers as $a) {
        if ($a['etablissement']) {
            $list[] = '<a href="'.url_for('dr_redirect', array('identifiant' => $a['etablissement']->identifiant, 'campagne' => $dr->campagne)).'">'.$a['etablissement']->raison_sociale.'</a>';
        }else{
            $list[] = $a['raison_sociale'].' ('.$a['cvi'].')';
        }
    }
    echo implode(', ', $list);
?>
    </p>
<?php endif; ?>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?= ($service) ?: url_for('declaration_etablissement', ['identifiant' => $dr->identifiant, 'campagne' => $dr->campagne]) ?>"
            class="btn btn-default"
        >
            <i class="glyphicon glyphicon-chevron-left"></i> Retour
        </a>
    </div>

    <div class="col-xs-4 text-center">
        <a class="btn btn-default" href="<?php echo url_for('get_fichier', array('id' => $dr->_id)) ?>">
            <i class="glyphicon glyphicon-file"></i> PDF de la <?php echo $dr->type ; ?>
        </a>
    </div>
<div class="col-xs-4 text-right">
<?php if(DRConfiguration::getInstance()->hasValidationDR()): ?>
        <?php if ($sf_user->isAdmin()): ?>
            <?php if($dr->exist('validation_odg') && $dr->validation_odg): ?>
                <a class="btn btn-default btn-sm" href="<?= url_for('dr_devalidation', $dr) ?>"
                    onclick="return confirm('Êtes vous sûr de vouloir dévalider cette DR');"
                >
                    <span class="glyphicon glyphicon-remove-sign"> Dévalider</span>
                </a>
            <?php elseif(isset($validation) && $validation->hasErreurs()) : ?>
                <a href="#" class="btn btn-default disabled">
                    Approuver la <?php echo $dr->type ; ?>
                </a>
            <?php else : ?>
                <a href="<?= url_for('dr_enattente_admin', ['id' => $dr->_id]) ?>" class="btn btn-default">
                    <?= ($dr->exist('statut_odg') && $dr->statut_odg) ? 'Enlever la mise en attente' : 'Mise en attente' ?>
                </a>
                <a href="<?= url_for('dr_approbation', ['id' => $dr->_id]) ?>" class="btn btn-success">
                    Valider la <?php echo $dr->type ; ?>
                </a>
            <?php endif ?>
        <?php endif ?>
<?php endif; ?>
<?php if ($sf_user->isAdmin() && $dr->isDeletable()): ?>
    <a href="<?= url_for('dr_suppression', ['id' => $dr->_id]) ?>" class="btn text-danger" onclick="return confirm('Etes vous sur de vouloir supprimer ce document ?');">
        Supprimer la <?php echo $dr->type ; ?>
    </a>
<?php endif; ?>
</div>
</div>
