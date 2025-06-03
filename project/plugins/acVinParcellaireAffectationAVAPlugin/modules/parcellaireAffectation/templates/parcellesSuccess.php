<?php use_helper('Float'); ?>
<?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaire' => $parcellaire )); ?>
<?php include_partial('step', array('step' => 'parcelles', 'parcellaire' => $parcellaire));
$isVtSgn = is_string($appellationNode) && ($appellationNode == ParcellaireAffectationClient::APPELLATION_VTSGN);
?>

<div class="page-header">
    <h2>Saisie des <?php if ($parcellaire->isIntentionCremant()): ?>intentions de production<?php else: ?>parcelles<?php endif; ?><?php echo ($parcellaire->isParcellaireCremant()) ? ' de Crémant' : ''; ?></h2>
</div>

<?php if(count($parcellaire->declaration->getProduitsCepageDetails())): ?>
<a href="<?php echo url_for('parcellaire_scrape_douane', array('sf_subject' => $parcellaire->getEtablissementObject(), 'url' => url_for('parcellaire_parcelles_update_cvi', array('id' => $parcellaire->_id, 'appellation' => $appellation)))) ?>" class="btn btn-sm btn-warning pull-right ajax" style="margin-bottom: 10px;"><i class="glyphicon glyphicon-refresh"></i> Mettre à jour les parcelles via Prodouane</a>
<?php endif; ?>

<ul style="margin-top: 0;" class="nav nav-tabs">
    <?php
    $selectedAppellationName = "";
    foreach ($parcellaireAppellations as $appellationKey => $appellationName) :
        $isSelectedAppellation = ($appellation == $appellationKey);
        if (!$selectedAppellationName && $isSelectedAppellation) {
            $selectedAppellationName = $appellationName;
        }
        ?>
        <li role="presentation" class="<?php echo ($isSelectedAppellation) ? 'active' : '' ?>"><a href="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellationKey)) ?>" class="ajax"><?php echo $appellationName; ?></a></li>
    <?php endforeach; ?>
</ul>

<?php if ($recapParcellaire): ?>
	<h3>Parcelles déjà déclarées dans votre affectation parcellaire Crémant <small>(celles-ci ne sont pas à redéclarer)</small></h3>
	<div id="bloc_recap" style="height: 160px; overflow: hidden; position: relative;">
		<?php include_partial('parcellaireAffectation/recap', array('parcellaire' => $recapParcellaire, 'notitle' => true)); ?>
		<div style="width: 100%; position: absolute; height: 70px; bottom: 0;  background: linear-gradient(to bottom, transparent, white);"></div>
	</div>
	<div class="text-center">
		<button id="btn_recap_voir_tout" class="btn btn-sm btn-link"><span class="glyphicon glyphicon-chevron-down"></span> Voir tout</button>
		<button id="btn_recap_voir_moins" class="btn btn-sm btn-link hidden"><span class="glyphicon glyphicon-chevron-up"></span> Voir moins</button>
	</div>
	<script>
		document.getElementById('btn_recap_voir_tout').addEventListener('click', function() {
			this.classList.add('hidden');
			document.getElementById('btn_recap_voir_moins').classList.remove('hidden')
			document.getElementById('bloc_recap').style.height = 'auto';
		});

		document.getElementById('btn_recap_voir_moins').addEventListener('click', function() {
			this.classList.add('hidden');
			document.getElementById('btn_recap_voir_tout').classList.remove('hidden')
			document.getElementById('bloc_recap').style.height = '160px';
		});
	</script>
<?php endif; ?>

<?php if ($sf_user->hasFlash('warning')): ?>
    <div class="alert alert-warning" role="alert"><?php echo $sf_user->getFlash('warning') ?></div>
<?php endif; ?>

<form action="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellation)); ?>" method="post" class="form-horizontal ajaxForm parcellaireForm">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row">
        <?php if ($appellation == ParcellaireAffectationClient::APPELLATION_VTSGN): ?>
            <div class="col-xs-12">
                <p><strong>&nbsp;Pour affecter une parcelle en mention VT ou SGN, cliquez sur la ligne.</strong></p>
            </div>
        <?php endif; ?>
        <div class="col-xs-12">
			<p style="margin-top: -10px; margin-bottom: 5px;"><a href="javascript:void(0)" class="bootstrap-switch-activeall" data-target="#listes_cepages" style="display: none;"><span class='glyphicon glyphicon-check'></span>&nbsp;Séléctionner toutes les parcelles</a><a href="javascript:void(0)" class="bootstrap-switch-removeall" data-target="#listes_cepages" style="display: none;"><span class='glyphicon glyphicon-remove'></span>&nbsp;Désélectionner toutes les parcelles</a></p>
            <div id="listes_cepages" class="list-group">
                <?php if (count($parcelles)) : ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="col-xs-1 text-center">Affectée</th>
                                <th class="col-xs-3 text-center">Appellation</th>
                                <th class="col-xs-2 text-center">Commune</th>
                                <th class="col-xs-1 text-center">Section / Numéro</th>
                                <?php if(!is_object($appellationNode) || $appellationNode->getConfig()->hasLieuEditable()):  ?>
                                <th class="col-xs-2 text-center">Lieu-dit revendiqué
                                    <?php if(is_object($appellationNode) && strpos($appellationNode->getHash(), 'CREMANT') === null): ?>
                                    <p class="small text-muted" style="margin:0;">Lieu-dit cadastral</p>
                                    <?php endif; ?>
                                </th>
                                <?php endif; ?>
								<th class="col-xs-2 text-center">Cépage</th>
                                <th class="col-xs-1 text-center">Superficie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tabindex = 1;
                            foreach ($parcelles as $key => $parcelle):
                                $attention_ret = ($attention && ($attention == $parcelle->getHashForKey()));
                                $erreur_ret = ($erreur && ($erreur == $parcelle->getHashForKey()));
                                $class = ($erreur_ret || $attention_ret) ? 'error_field_to_focused' : '';
                                $styleErr = ($attention_ret) ? 'style="border-style: solid; border-width: 1px; border-color: darkorange;"' : "";
                                $styleWar = ($erreur_ret) ? 'style="border-style: solid; border-width: 1px; border-color: darkred;"' : "";
                                ?>
                                <tr <?php echo $styleErr . $styleWar; ?> >
                                    <td class="text-center">
                                        <?php
                                        if (isset($form['produits'][$parcelle->getHashForKey()]['vtsgn'])) {
                                            echo $form['produits'][$parcelle->getHashForKey()]['vtsgn']->render();
                                        } else {
                                            echo $form['produits'][$parcelle->getHashForKey()]['active']->render();
                                        }
                                        ?>
                                    </td>
									<td><?php echo $parcelle->getAppellationLibelle((isset($form['produits'][$parcelle->getHashForKey()]['vtsgn']))); ?></td>
                                    <td><?php echo $parcelle->getCommune(); ?></td>
                                    <td class="text-right"><?php echo $parcelle->getSection(); ?> <?php echo $parcelle->getNumeroParcelle(); ?></td>
                                    <?php if(!is_object($appellationNode) || $appellationNode->getConfig()->hasLieuEditable()):  ?>
                                        <td>
                                            <?php echo $parcelle->getLieuLibelle() ? $parcelle->getLieuLibelle() : '<p style="margin:0;"> - </p>'; ?>
                                            <?php if($parcelle->getLieuDitCadastral() && strpos($parcelle->getProduitHash(), 'LIEUDIT')) : ?>
                                                <p class="small text-muted" style="margin:0;"><?php echo $parcelle->getLieuDitCadastral() ?></p>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <td><?php echo $parcelle->getCepageLibelle(); ?></td>
                                    <td class="edit text-right" style="position: relative;">
                                        <?php echoFloatFr($parcelle->getSuperficie(ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE), 2) ?> <small class="text-muted">ares</small>
                                        <span style="position: absolute; right: -20px;">
                                        <?php if (!$isVtSgn || $parcelle->isFromAppellation(ParcellaireAffectationClient::APPELLATION_ALSACEBLANC)): ?>
                                           <a class="btn btn-link btn-xs ajax" href="<?php echo url_for('parcellaire_parcelle_modification', array('id' => $parcellaire->_id, 'appellation' => $appellation, 'parcelle' => $parcelle->getHashForKey())); ?>" ><span class="glyphicon glyphicon-pencil"></span></a>
                                        <?php else: ?>
                                           <span class="btn btn-link btn-xs opacity-md" data-toggle="tooltip" title="Cette parcelle provient d'un autre onglet, elle n'est modifiable qu'à son origine"><span class="glyphicon glyphicon-pencil"></span></span>
                                       <?php endif; ?>
                                       </span>
                                    </td>
                                </tr>
                                <?php
                                $tabindex++;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif(!count($parcellaire->declaration->getProduitsCepageDetails())): ?>
				<p class="text-muted">Nous n'avons trouvé aucune parcelle, vous pouvez <a class="btn btn-default" href="<?php echo url_for('parcellaire_scrape_douane', array('sf_subject' => $parcellaire->getEtablissementObject(), 'url' => url_for('parcellaire_parcelles_update_cvi', array('id' => $parcellaire->_id, 'appellation' => $appellation)))) ?>"><i class="glyphicon glyphicon-refresh"></i> Récupérer vos parcelles depuis Prodouane</a></p>
			<?php else: ?>
                <p class="text-muted">Vous n'avez aucune <?php if ($parcellaire->isIntentionCremant()): ?>intention de production<?php else: ?>parcelle<?php endif; ?> à affecter dans cette appellation.</p><br/>
            <?php endif; ?>
            <?php if($sf_user->isAdmin()): ?>
            <div class="text-left">
                <button class="btn btn-sm btn-warning ajax" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter une parcelle</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <?php if ($isVtSgn) : ?>
                <a href="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => "GRDCRU")); ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
            <?php elseif ($appellationNode->getPreviousAppellationKey()) : ?>
                <a href="<?php echo url_for('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellationNode->getPreviousAppellationKey())); ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
            <?php else : ?>
                <a href="<?php echo url_for("parcellaire_exploitation", $parcellaire) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
            <?php endif; ?>
        </div>
        <div class="col-xs-6 text-right">
            <?php if ($parcellaire->exist('etape') && $parcellaire->etape == ParcellaireAffectationEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Retourner <small>à la validation</small></button>
            <?php elseif (!$isVtSgn && $appellationNode->getNextAppellationKey()): ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper btn-default-step">Continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper btn-default">Continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
                <?php endif; ?>
        </div>
    </div>
</form>

<?php include_partial('parcellaireAffectation/popupAjoutForm', array('url' => url_for('parcellaire_parcelle_ajout', array('id' => $parcellaire->_id, 'appellation' => $appellation)), 'form' => $ajoutForm, 'appellation' => $appellation)); ?>
