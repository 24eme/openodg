<?php use_helper('Float') ?>
<?php use_helper('PointsAides'); ?>

<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => DrevEtapes::ETAPE_REVENDICATION, 'drev' => $drev, 'ajax' => true)) ?>

<?php
$global_error_with_infos = "";
foreach ($form->getGlobalErrors() as $item):
$global_error_with_infos = $item->getMessage();
break;
endforeach;

$hasError = ($global_error_with_infos != "");
$global_error_id = substr($global_error_with_infos, 0, strrpos($global_error_with_infos, ']') + 1);
$global_error_msg = str_replace($global_error_id, '', $global_error_with_infos);
?>
<div class="page-header">
    <?php if($drev->hasDR()): ?>
        <!--<a class="btn btn-sm btn-default-step pull-right" href="<?php echo url_for("drev_dr_recuperation", $drev) ?>"><span class="glyphicon glyphicon-refresh"></span>&nbsp;&nbsp;Recharger les données de la Déclaration de Récolte</a>-->
    <?php endif; ?>
    <?php if(!$drev->isNonRecoltant() && !$drev->hasDR()): ?>
        <!--<a class="btn btn-warning btn-sm pull-right" href="<?php echo url_for("drev_dr_recuperation", $drev) ?>"><span class="glyphicon glyphicon-upload"></span>&nbsp;&nbsp;Récupérer les données de la Déclaration de Récolte</a>-->
    <?php endif; ?>
    <h2>Revendication AOP</h2>
</div>

<?php echo include_partial('global/flash'); ?>

<form role="form" action="<?php echo url_for("drev_revendication", $drev) ?>" method="post" class="ajaxForm" id="form_revendication_drev_<?php echo $drev->_id; ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php if ($hasError): ?>
    <div class="alert alert-danger" role="alert"><?php echo $global_error_msg; ?></div>
    <?php endif; ?>
    <p>Les informations de revendication sont reprises depuis votre Déclaration de Récolte, SV11 ou SV12, lorsque nous avons pu déduire vos volumes sur place.
    <br /><br />Veuillez vérifier leur cohérence et au besoin compléter les informations manquantes.</p>
    <?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('erreur')): ?>
    <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
    <?php endif; ?>
    <table class="table table-bordered table-striped table-condensed" id="table-revendication">
        <thead>
            <tr>
                <th class="text-center col-xs-2"></th>
<?php if ($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR): ?>
                <th colspan="4" class="text-center info"><?php echo $drev->getDocumentDouanierTypeLibelle(); ?></th>
                <th colspan="<?php echo ($drev->hasProduitWithMutageAlcoolique()) ? "4" : "3" ?>" class="text-center">Déclaration de Revendication</th>
<?php elseif ($drev->getDocumentDouanierType() == SV11CsvFile::CSV_TYPE_SV11): ?>
                <th colspan="2" class="text-center info"><?php echo $drev->getDocumentDouanierTypeLibelle(); ?></th>
                <th colspan="<?php echo ($drev->hasProduitWithMutageAlcoolique()) ? "4" : "3" ?>" class="text-center">Déclaration de Revendication</th>
<?php else: ?>
                <th class="text-center info"><?php echo $drev->getDocumentDouanierTypeLibelle(); ?></th>
                <th colspan="<?php echo ($drev->hasProduitWithMutageAlcoolique()) ? "3" : "2" ?>" class="text-center">Déclaration de Revendication</th>
<?php endif; ?>
            </tr>
            <tr>
<?php if ($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR): ?>
                <th class="col-xs-3"><?php if (count($form['produits']) > 1): ?>Produits revendiqués<?php else: ?>Produit revendiqué<?php endif; ?></th>
                <th class="text-center info col-xs-1" style="position: relative;">Volume récolté total<br/>(L5)<br/><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_recolte_total') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="text-center info col-xs-1" style="position: relative;">Volume en cave part.<br/>(L9)<br/><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_cave_particuliere') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="text-center info col-xs-1" style="position: relative;">Vol. récolté net totale<br/>(L15)<br/><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'recolte_nette_totale') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="text-center info col-xs-1" style="position: relative;">Volume VCI constitué<br/>(L19)<br/><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'vci_constitue') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-1 text-center" style="position: relative;">Volume <br/>revendiqué net <br />issu de la récolte<br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_net_issu_recolte') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <?php if($drev->hasProduitWithMutageAlcoolique()): ?>
                <th class="col-xs-1 text-center" style="position: relative;">Volume d’alcool ajoutée<br />pour le mutage<br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_issu_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <?php endif; ?>
                <th class="col-xs-1 text-center" style="position: relative;">Volume revendiqué<br />issu du VCI <br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_issu_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-1 text-center" style="position: relative;">Volume revendiqué net total<br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_net_total') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
<?php elseif ($drev->getDocumentDouanierType() == SV11CsvFile::CSV_TYPE_SV11): ?>
                <th class="col-xs-3"><?php if (count($form['produits']) > 1): ?>Produits revendiqués<?php else: ?>Produit revendiqué<?php endif; ?></th>
                <th class="text-center info col-xs-1" style="position: relative;">Volume<br/>en cave<br/><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_cave_particuliere') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="text-center info col-xs-1" style="position: relative;">Volume VCI<br/>constitué<br/><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'vci_constitue') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-1 text-center" style="position: relative;">Volume <br/>revendiqué net <br />issu de récoltes<br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_net_issu_recolte') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <?php if($drev->hasProduitWithMutageAlcoolique()): ?>
                <th class="col-xs-1 text-center" style="position: relative;">volume d’alcool<br/>ajouté pour le mutage <br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_issu_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <?php endif; ?>
                <th class="col-xs-1 text-center" style="position: relative;">Volume revendiqué<br />issu du VCI <br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_issu_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-1 text-center" style="position: relative;">Volume revendiqué net total<br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_net_total') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
<?php else: ?>
                <th class="col-xs-4"><?php if (count($form['produits']) > 1): ?>Produits revendiqués<?php else: ?>Produit revendiqué<?php endif; ?></th>
                <th class="text-center info col-xs-2" style="position: relative;">Volume en cave<br/><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_cave_particuliere') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-2 text-center" style="position: relative;">Volume <br/>revendiqué net <br />issu de récoltes<br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_net_issu_recolte') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute  ; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <?php if($drev->hasProduitWithMutageAlcoolique()): ?>
                <th class="col-xs-2 text-center" style="position: relative;">Volume revendiqué<br />issu du mutage <br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_issu_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <?php endif; ?>
                <th class="col-xs-2 text-center" style="position: relative;">Volume revendiqué net total<br /><small class="text-muted">(hl)</small><a title="<?php echo getPointAideText('drev', 'volume_revendique_net_total') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
<?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($form['produits'] as $key => $embedForm): ?>
                <?php $produit = $drev->get($key); ?>
                <?php include_partial("drev/revendicationForm", array('produit' => $produit, 'form' => $embedForm, 'drev' => $drev, 'appellation' => $appellation, 'global_error_id' => $global_error_id, 'vtsgn' => false)); ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-4">

			 <a href="<?php echo (count($drev->getProduitsLots()) > 0) ? url_for('drev_lots', $drev) : ((count($drev->getProduitsLots()) > 0) ? url_for('drev_vci', $drev) : url_for('drev_revendication_superficie', $drev)) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
                <div class="btn-group">
                    <?php if ($sf_user->hasDrevAdmin()): ?>
                      <a href="<?php echo url_for('drev_document_douanier_pdf', $drev); ?>" class="btn btn-default <?php if(!$drev->hasDocumentDouanier()): ?>disabled<?php endif; ?>" >
                          <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;<?php echo $drev->getDocumentDouanierType() ?>
                      </a>
                    <?php endif; ?>
			        <a href="<?php echo url_for('drev_revendication_reset', $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-refresh"></span> recalculer les volumes</a>
               </div>
        </div>
        <div class="col-xs-4 text-right">
                <button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>
