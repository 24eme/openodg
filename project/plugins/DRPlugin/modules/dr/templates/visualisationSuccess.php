<?php use_helper('Date'); ?>

<?php include_partial('global/flash'); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $dr->identifiant, 'campagne' => $dr->campagne)); ?>"><?php echo $dr->getEtablissementObject()->getNom() ?> (<?php echo $dr->getEtablissementObject()->identifiant ?> - <?php echo $dr->getEtablissementObject()->cvi ?>)</a></li>
  <li><a class="active" href=""><?php if($dr->isBailleur()) echo "Synthèse bailleur "; else echo $dr->type; ?> de <?php echo $dr->getperiode(); ?></a></li>
</ol>


<div class="page-header no-border">
    <h2>
        <?php if ($dr->isBailleur()): ?>
            <?php
            echo 'Synthèse bailleur de Récolte '.$dr->campagne;
            ?>
        <?php else: echo 'Déclaration'; ?>
            <?php if ($dr->type == 'DR') echo 'de Récolte'; else echo $dr->type; ?> <?= $dr->campagne ?>
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
        <?php endif; ?>
    </h2>
</div>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $dr->getEtablissementObject()]); ?>
</div>

<?php if (!$dr->isBailleur() && isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('dr/pointsAttentions', ['validation' => $validation]); ?>
<?php endif ?>

<?php use_helper('Float') ?>

<?php if ($dr->type == 'SV11' || $dr->type == 'SV12'): ?>
    <a class="btn btn-sm btn-default pull-right" href="<?php echo url_for('sv_verify', array('id' => $dr->_id)) ?>">
        <i class="glyphicon glyphicon-search"></i> Comparer les volumes avec les DR
    </a>
<?php else: ?>
    <?php if (class_exists("ParcellaireAffectation") && in_array('parcellaireAffectation', sfConfig::get('sf_enabled_modules'))) : ?>
    <a class="btn btn-sm btn-default pull-right" href="<?php echo url_for('dr_verify', array('id' => $dr->_id)) ?>">
        <i class="glyphicon glyphicon-search"></i> Comparer les superficies avec la DAP
    </a>
    <?php endif; ?>
<?php endif; ?>
<?php if ($sf_user->isAdminOdg() && !$dr->isBailleur()): ?>
    <div class="btn-group pull-right" style="margin-right: 1rem">
      <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdown-declassement" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        Déclassement <?php echo $dr->type ?>
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu" aria-labelledby="dropdown-declassement">
        <?php foreach ($dr->getProduitsDetail()['produits'] as $produit): ?>
          <?php if (strpos($produit['libelle'], "déclassé") !== false): ?>
              <?php continue; ?>
          <?php endif ?>
          <li>
            <a href="<?php echo url_for('chgtdenom_create_from_production', ['identifiant' => $dr->identifiant, 'campagne' => $dr->campagne, 'hash_produit' => $produit['hash'], 'complement' => isset($produit['complement'])? $produit['complement']:null]) ?>">
              Déclassement <?php echo $dr->type ?> <?php echo $produit['libelle'] ?>
            </a>
          </li>
        <?php endforeach ?>
      </ul>
    </div>
<?php endif ?>


<h3 class="text-left">Détail par produit</h3>


<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-5" style="border-top: hidden; border-left: hidden"></th>
            <th colspan="12" class="text-center">Lignes</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th class="text-center col-xs-5 clearfix">Produits
                <?php if ($dr->getDocumentDefinitionModel() == 'DR'): ?>
                    <small class="pull-right text-muted">Rdmt L5-L16|L15</small>
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
        <?php $isDeclasse = isset($produit['complement']) && strpos($produit['complement'], 'déclassé') !== false; ?>
            <tr <?php if ($isDeclasse): ?>class="bg-warning" style="opacity: 0.6"<?php endif ?>>
                <td>
                    <strong><?= $produit['libelle'] ?></strong>
                    <?php if ($dr->isBailleur()): ?>
                        <br />
                        <small class="pull-left">
                            <span>
                                <?php echo $produit['metayers']['declarant_raison_sociale']; ?>
                            </span>
                        </small>
                    <?php endif; ?>
                    <br />
                    <?php if ($isDeclasse === false): ?>
                    <small class="pull-right text-muted">
                        <?php if ($dr->getDocumentDefinitionModel() == 'DR'): ?>
                            <span title="Rendement L5" style="cursor: help">
                                <?php if ($produit['lignes']['05']['val'] > 0 && $produit['lignes']['04']['val'] > 0): ?>
                                    <?php echoFloatFr(round( ($produit['lignes']['05']['val'] * 1 - $produit['lignes']['16']['val'] * 1) / $produit['lignes']['04']['val'], 2)); ?>
                                <?php else: echoFloatFr(0) ?>
                                <?php endif ?>
                            </span> hl/ha
                            |
                        <?php endif ?>
                        <span title="Rendement L15" style="cursor: help">
                            <?php if ($produit['lignes']['15']['val'] > 0 && $produit['lignes']['04']['val'] > 0): ?>
                                <?php echoFloatFr( round(intval($produit['lignes']['15']['val']) / $produit['lignes']['04']['val'], 2) ) ;?>
                            <?php else: echoFloatFr(0); ?>
                            <?php endif ?>
                        </span> hl/ha
                    </small>
                    <?php endif ?>
                </td>
                <?php foreach ($produit['lignes'] as $l => $p): ?>
                    <td class="text-right" title="Ligne L<?= $l ?>">
                        <?= ($p['val'] === null) ? '—' : echoFloat($p['val']) ?> <span class="text-muted"><?= $p['unit'] ?? '' ?></span>
                    </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        <?php if(isset($produit)): ?>
            <tr>
                <th class="text-right"><strong>Total</strong></th>
                <?php foreach ($produit['lignes'] as $l => $p): ?>
                    <th class="text-right"><strong>
                        <?php if ($dr->isBailleur()): ?>
                            <?php echoFloat($dr->getTotalValeur($l, null, null, null, array(), false)) ?></strong>&nbsp;<span class='text-muted'><?= $p['unit'] ?></span></th>
                        <?php else: ?>
                            <?php echoFloat($dr->getTotalValeur($l)) ?></strong>&nbsp;<span class='text-muted'><?= $p['unit'] ?></span></th>
                        <?php endif; ?>
                    <?php endforeach ?>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($dr->isBailleur()): ?>
        <?php $metayers = $dr->getMetayers()->getRawValue(); ?>
        <?php if(count($metayers)): ?>
            <p style="margin-top: -10px; margin-bottom: 20px;">
                Ces volumes ont été récoltés par les métayers
                <?php
                $metayers_list = array();
                foreach($metayers as $b) {
                    if (!$b['etablissement_id']) {
                        continue;
                    }
                    $etablissement_metayer = EtablissementClient::getInstance()->findByCvi($b['cvi']);
                    $dr_metayer = DRClient::getInstance()->findByArgs($etablissement_metayer->identifiant, $dr->campagne);
                    $metayers_list[] = '<a href="'. url_for('dr_visualisation', array('id' => $dr_metayer->_id)).'">'.$b['raison_sociale'].'</a>';
                }
                echo implode(', ', $metayers_list);
                ?>
            </p>
        <?php endif; ?>
    <?php else: ?>
        <?php $bailleurs = $dr->getBailleurs()->getRawValue(); ?>
        <?php if(count($bailleurs)): ?>
            <p style="margin-top: -10px; margin-bottom: 20px;">
                Ces volumes ont été récoltés pour le compte <?php if(count($bailleurs) > 1): ?>des<?php else: ?>du<?php endif; ?> bailleur<?php if(count($bailleurs) > 1): ?>s :<?php endif; ?>
                    <?php foreach($bailleurs as $b): ?>
                        <?php  if (!$b['etablissement_id']): continue; endif; ?>
                        <a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $b['etablissement_id'], 'campagne' => $dr->campagne)) ?>"><?php echo $b['raison_sociale']; ?></a>
                    <?php endforeach; ?>. Ces volumes ne figurent pas dans le tableau.
                </p>
            <?php endif; ?>
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

    <?php if (count($chgtsProd)): ?>
        <p style="margin-top: -10px; margin-bottom: 20px;">
            Ce document à <?php echo count($chgtsProd) ?> <?php if (count($chgtsProd) > 1): ?> déclassements <?php else: ?> déclassement <?php endif ?>sans revendication :
            <ul>
                <?php foreach ($chgtsProd as $c): ?>
                <li><a href="<?php echo url_for('chgtdenom_visualisation', ['id' => $c->_id]) ?>"><?php echo $c->origine_produit_libelle ?><?php echo $c->origine_specificite ? " ".str_replace('déclassé', '', $c->origine_specificite) : null ?></a>
                    (<span style="text-decoration: underline dotted;cursor: help;" title="Volume imputé sur la L15">- <?php echo $c->origine_volume ?> hl</span>)
                </li>
                <?php endforeach ?>
            </ul>
        </p>
    <?php endif ?>

            <div class="row row-margin row-button">
                <div class="col-xs-4">
                    <a href="<?= (isset($service) && $service) ?: url_for('declaration_etablissement', ['identifiant' => $dr->identifiant, 'campagne' => $dr->campagne]) ?>"
                        class="btn btn-default"
                        >
                        <i class="glyphicon glyphicon-chevron-left"></i> Retour
                    </a>
                </div>

                <?php if (!$dr->isBailleur()): ?>
                    <div class="col-xs-4 text-center">
                        <a class="btn btn-default" href="<?php echo url_for('get_fichier', array('id' => $dr->_id)) ?>">
                            <i class="glyphicon glyphicon-file"></i> PDF de la <?php echo $dr->type ; ?>
                        </a>
                    </div>
                    <div class="col-xs-4 text-right">
                        <?php if(DRConfiguration::getInstance()->hasValidationDR()): ?>
                            <?php if ($sf_user->isAdminODG()): ?>
                                <?php if($dr->exist('validation_odg') && $dr->validation_odg): ?>
                                    <a class="btn btn-default btn-sm" href="<?= url_for('dr_devalidation', $dr) ?>"
                                        onclick="return confirm('Êtes vous sûr de vouloir dévalider cette <?php echo $dr->getType() ?>');"
                                        >
                                        <span class="glyphicon glyphicon-remove-sign"></span> Dévalider
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
                        <?php if ($sf_user->isAdminODG() && $dr->isDeletable()): ?>
                            <a href="<?= url_for('dr_suppression', ['id' => $dr->_id]) ?>" class="btn text-danger" onclick="return confirm('Etes vous sur de vouloir supprimer ce document ?');">
                                Supprimer la <?php echo $dr->type ; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
