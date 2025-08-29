<?php use_helper('Float') ?>
<?php use_helper('Date') ?>

<?php
$coop_id = null;
if(isset($coop)):
    $coop_id = explode('-', $coop)[1];
    ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php else: ?>
    <?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>
<?php endif; ?>

<?php include_partial('parcellaireAffectation/step', array('step' => 'affectations', 'parcellaireAffectation' => $parcellaireAffectation)) ?>

<h2>Affectation de vos parcelles</h2>

<?php $parcellaire2reference = $parcellaireAffectation->getParcellaire2Reference(); ?>
<?php if ($parcellaire2reference): ?>
<p>Les parcelles listées ci-dessous sont reprises
<?php if (strpos($parcellaire2reference->_id, 'PARCELLAIRE-') !== false) : ?>
    <a href="<?php echo url_for('parcellaire_visualisation', $parcellaire2reference); ?>">parcellaire douanier</a></p>
<?php else: ?>
    l'identification du <?php echo preg_replace('/([0-9]*)-([0-9]*)-([0-9]*)/', '\3/\2/\1', $parcellaire2reference->date);?>
<?php endif; ?>
, elles sont affectables par destination.</p>
<?php endif; ?>
<?php if(!$parcellaireAffectation->isAllPreviousParcellesExists()): ?>
    <div class="alert alert-warning">
        Toutes les parcelles affectées issues de <a href="<?php echo url_for('parcellaireaffectation_visualisation', $parcellaireAffectation->getPreviousDocument()) ?>">la déclaration de la précédente campagne</a> n'ont pas pu être reprises, il est conseillé de vérifier l'ensemble des parcelles affectées.
    </div>
<?php endif; ?>

<ul class="nav nav-tabs mt-4">
<?php foreach($destinataires as $id => $d):
    if (count($produits) > 1):
        foreach ($produits as $hash => $produit):
    ?>
    <li role="presentation" class="<?php if($id.$hash == $destinataire.$hashproduit): ?>active<?php endif; ?><?php if ($coop_id && strpos($id, $coop_id) === false): ?>disabled<?php endif; ?>">
        <a class="onglet-presentation" data-form="validation-form" data-href="<?php echo url_for('parcellaireaffectation_affectations', ['sf_subject' => $parcellaireAffectation, 'destinataire' => $id, 'hashproduit' => $hash]) ?>" href='#'>
            <?php if($id == $parcellaireAffectation->getEtablissementObject()->_id): ?><span class="glyphicon glyphicon-home"></span> <?php endif; ?><?php
            echo ($d['libelle_etablissement'] != 'Cave particulière') ? $d['libelle_etablissement'].' - ' : '';
            echo $produit; ?>
        </a>
    </li>
    <?php
        endforeach;
    else:
    ?>
    <li role="presentation" class="<?php if($id == $destinataire): ?>active<?php endif; ?><?php if ($coop_id && strpos($id, $coop_id) === false): ?>disabled<?php endif; ?>"><a class="onglet-presentation" data-form="validation-form" data-href="<?php echo url_for('parcellaireaffectation_affectations', ['sf_subject' => $parcellaireAffectation, 'destinataire' => $id]) ?>" href="#"><?php if($id == $parcellaireAffectation->getEtablissementObject()->_id): ?><span class="glyphicon glyphicon-home"></span> <?php endif; ?><?php echo $d['libelle_etablissement'] ?></a></li>
    <?php endif; ?>
<?php endforeach; ?>
</ul>

<form id="validation-form" action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <?php $has_parcelles = false; ?>
    <?php foreach ($parcellaireAffectation->getGroupedParcelles(false, $hashproduit) as $group => $parcelles):?>
    <?php if ($group): ?>
        <div style="margin-bottom: 1em;" class="row">
            <div class="col-xs-6">
                <h3><?php if ($parcellaireAffectation->hasDgc()): ?>Dénomination <?php endif; ?><?php echo $group; ?></h3>
            </div>
            <div class="col-xs-6">
               <p class="text-right" style="margin-top: 30px;"><a href="javascript:void(0)" class="bootstrap-switch-activeall" data-target="#parcelles_<?php echo $group; ?>" style="display: none;"><span class='glyphicon glyphicon-check'></span>&nbsp;Toutes les parcelles de cette <?php if ($parcellaireAffectation->hasDgc()): ?>dénomination<?php else: ?>commune<?php endif; ?></a><a href="javascript:void(0)" class="bootstrap-switch-removeall" data-target="#parcelles_<?php echo $group; ?>" style="display: none;"><span class='glyphicon glyphicon-remove'></span>&nbsp;Désélectionner toutes les parcelles de cette  <?php if ($parcellaireAffectation->hasDgc()): ?>dénomination<?php else: ?>commune<?php endif; ?></a></p>
           </div>
        </div>
    <?php endif; ?>
    <table id="parcelles_<?php echo $group; ?>" class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
    	<thead>
        	<tr>
        		<th class="col-xs-3">Commune</th>
                <th class="col-xs-2">Lieu-dit</th>
                <th class="col-xs-1">Section /<br />N° parcelle</th>
                <th class="col-xs-2">Cépage</th>
                <th class="col-xs-1 text-center">Année plantat°</th>
                <th class="col-xs-1 text-right">Surf. totale <span class="text-muted small">(ha)</span></th>
                <th class="col-xs-1 text-right">Surf. dédiée&nbsp;<span class="text-muted small">(ha)</span></th>
                <th class="col-xs-1">Affectée?</th>
                <th class="col-xs-1">Affectation</th>

            </tr>
    	</thead>
    	<tbody>
    	<?php
      $has_parcelles = true;
      $parcelles = $parcelles->getRawValue();
      ksort($parcelles);
    		foreach ($parcelles as $parcelle):
            if (isset($form[$parcelle->getParcelleId()])):
    	?>
    		<tr class="vertical-center" id="tr_<?php echo $parcelle->getParcelleId();?>">
    			<td><?php echo $parcelle->commune; ?></td>
                <td><?php echo $parcelle->lieu; ?></td>
                <td style="text-align: center;"><?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?></td>
                <td><?php echo $parcelle->cepage; ?></td>
                <td class="text-center"><?php echo $parcelle->campagne_plantation; ?></td>
                <td class="text-right"><?php echoFloatFr($parcelle->getSuperficieParcellaire(),4); ?></td>
                <td class="text-right edit">
                    <?php echo $form[$parcelle->getParcelleId()]['superficie']->render(); ?>
                </td>
            	<td class="text-center">
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$parcelle->getParcelleId()]['affectee']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$parcelle->getParcelleId()]['affectee']->renderError() ?>
                        <div class="col-xs-12">
    		            	<?php echo $form[$parcelle->getParcelleId()]['affectee']->render(array('class' => "bsswitch test", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                    </div>
            	</td>
                <td class="text-center">
                    <?php if ($parcelle->isAffectee()): ?>
                        <?php if ($parcelle->isPartielle()): ?><span>Partielle</span><?php else: ?><span>Totale</span><?php endif; ?>
                    <?php endif;?>
                </td>
            </tr>
        <?php  endif; endforeach; ?>
        <tr class="commune-total">
            <td colspan="6" class="text-right"><strong>Total <?php echo $group ?></strong></td>
            <td class="text-right"></td>
            <td class="text-right"></td>
            <td></td>
        </tr>
        </tbody>
    </table>
    <?php endforeach; ?>
    <?php
    if ($has_parcelles && $hashproduit):
        $superficie_potentielle = $parcellaireAffectation->getTheoriticalPotentielForHash($hashproduit);
        if ($superficie_potentielle):
    ?>
    <span id="PPvalid" class="pull-right label label-success mt-2 hidden"><span class="glyphicon glyphicon-ok-circle"></span> Le potentiel de production est respecté</span>
    <span id="PPinvalid" class="pull-right label label-danger mt-2 hidden"><span class="glyphicon glyphicon-warning-sign"></span> Le potentiel de prodution n'est pas respecté</span>
        <h3>Vérification du potentiel de production des parcelles affectées</h3>
        <table id="synthese-total" class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
                <tr>
                    <td class="col-xs-9 text-right">Superficie potentielle max.</td>
                    <td class="col-xs-1 text-right" id="superficie_potentielle"><?php echoFloat($superficie_potentielle, 4); ?></td>
                    <td class="col-xs-2 text-center" colspan="2"> (<a href="<?php echo url_for('parcellaire_potentiel_visualisation', array('id' => $parcellaireAffectation->getParcellaire()->_id)); ?>">détail du potentiel</a>) </td>
                </tr>
<?php foreach ($parcellaireAffectation->getTheoriticalPotentielProductionProduit($hashproduit)->getRules() as $rule): ?>
    <tr class="potentiel-regles">
        <td class="col-xs-9 text-right"><span class="text-muted"><?php if ($rule->getRegleFonction() == 'ProportionSomme'){echo 'Proportion de';}else{echo 'Nombre de';} ?></span> <?php echo implode(', ', $rule->getCepages()->getRawValue()); ?></td>
        <td class="col-xs-1 text-right"></td>
        <td class="col-xs-1 text-right"></td>
        <td class="col-xs-1 text-left" data-rulefonction="<?php echo $rule->getRegleFonction(); ?>" data-rulesens="<?php echo $rule->getSens(); ?>" data-rulevalue="<?php if ($rule->getRegleFonction() == 'ProportionSomme'){echo $rule->getLimitPC()*100;}else{echo $rule->getLimit();} ?>"><?php echo $rule->getSens() . ' '?><?php if ($rule->getRegleFonction() == 'ProportionSomme'){echo $rule->getLimitPC()*100 . '%';}else{echo $rule->getLimit();} ?></td>
    </tr>
<?php endforeach;?>
                <tr class="total">
                    <td class="col-xs-9 text-right"><strong>Total affecté</strong></td>
                    <td class="col-xs-1 text-right"></td>
                    <td class="col-xs-1 text-right"></td>
                    <td class="col-xs-1 text-left">parcelle(s)</td>
                </tr>
        </table>
<?php endif; ?>
    <?php else: ?>
        <p class="m-5"><i>Pas de parcelles affectables trouvées : voir l'<a href="<?php echo url_for('habilitation_visualisation', $parcellaireAffectation->getHabilitation()); ?>">habilitation</a> ou le <a href="<?php echo url_for('parcellaire_visualisation',  $parcellaireAffectation->getParcellaire()); ?>">parcellaire</a></i></p>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function (e) {
            updateTotal = function (table) {
                let superficie = 0
                let checked = 0

                table.querySelectorAll("tbody tr:not(.commune-total)").forEach(function (tr) {
                    if (tr.querySelector('.bsswitch:checked')) {
                        superficie += parseFloat(tr.querySelector('td:nth-child(0n+7) input').value)
                        checked ++
                    }
                })
                table.querySelector('tr.commune-total td:nth-child(0n+2)').innerText = parseFloat(superficie, 4).toFixed(4)
                table.querySelector('tr.commune-total td:nth-child(0n+3)').innerText = checked
                let total_superficie = 0;
                let total_checked = 0;
                document.querySelectorAll('tr.commune-total td:nth-child(0n+2)').forEach(function(td) {
                    total_superficie += parseFloat(td.innerText);
                });
                document.querySelectorAll('tr.commune-total td:nth-child(0n+3)').forEach(function(td) {
                    total_checked += parseInt(td.innerText);
                });

                if(document.querySelector('#synthese-total')) {
                    document.querySelector('#synthese-total tr.total td:nth-child(0n+2)').innerText = total_superficie.toFixed(4);
                    document.querySelector('#synthese-total tr.total td:nth-child(0n+3)').innerText = total_checked;
                }
            };

            updateRules = function () {
                let produitArray = {};
                document.querySelectorAll("table.tableParcellaire tbody tr:not(.commune-total)").forEach(function (tr) {
                    if (tr.querySelector('.bsswitch:checked')) {
                        if (! produitArray[tr.querySelector('td:nth-child(0n+4)').innerText]) {
                            produitArray[tr.querySelector('td:nth-child(0n+4)').innerText] = 0;
                        }
                        produitArray[tr.querySelector('td:nth-child(0n+4)').innerText] += parseFloat(tr.querySelector('td:nth-child(0n+7) input').value);
                    }
                });

                document.querySelectorAll("tr.potentiel-regles").forEach(function (tr) {
                    let total = 0;
                    let produitCount = 0;
                    let produitsBruts = tr.querySelector('td:nth-child(0n+1)').innerText;
                    let produits = produitsBruts.substr(produitsBruts.indexOf('de ') + 3).split(',');
                    produits.forEach(function (i) {
                        i = i.trim();
                        if (produitArray[i]) {
                            total += produitArray[i];
                            produitCount++;
                        }
                    });

                    let valeurActuelle = 0;
                    let limitPC = 0;
                    let valueMax = 0;
                    let fonction = tr.querySelector('td:nth-child(0n+4)').dataset.rulefonction;
                    let sens = tr.querySelector('td:nth-child(0n+4)').dataset.rulesens;
                    let totalAffecte = document.querySelector('#synthese-total tr.total td:nth-child(0n+2)').innerText;

                    if (fonction == 'ProportionSomme') {
                        valeurActuelle = total.toFixed(4);
                        limitPC = tr.querySelector('td:nth-child(0n+4)').dataset.rulevalue / 100;
                        if (totalAffecte > 0) {
                            tr.querySelector('td:nth-child(0n+3)').innerText = Math.round((total.toFixed(4) / totalAffecte) * 100) + '%';
                        } else {
                            tr.querySelector('td:nth-child(0n+3)').innerText = '0%';
                        }
                        tr.querySelector('td:nth-child(0n+2)').innerText = total.toFixed(4);
                        valueMax = totalAffecte * limitPC;
                    } else {
                        valeurActuelle = produitCount;
                        limitPC = tr.querySelector('td:nth-child(0n+4)').dataset.rulevalue;
                        tr.querySelector('td:nth-child(0n+2)').innerText = produitCount;
                        valueMax = limitPC;
                    }

                    if (valueMax == 0) {
                        tr.classList = "potentiel-regles";
                    } else {
                        if (fonction == 'ProportionSomme') {
                            if (sens == '>=') {
                                if (valeurActuelle >= valueMax) {
                                    tr.classList = "potentiel-regles success";
                                } else {
                                    tr.classList = "potentiel-regles danger";
                                    isValid = 0;
                                }
                            } else {
                                if (valeurActuelle <= valueMax) {
                                    tr.classList = "potentiel-regles success";
                                } else {
                                    tr.classList = "potentiel-regles danger";
                                    isValid = 0;
                                }
                            }
                        } else {
                            if (sens == '>=') {
                                if (valeurActuelle >= valueMax) {
                                    tr.classList = "potentiel-regles success";
                                } else {
                                    tr.classList = "potentiel-regles danger";
                                    isValid = 0;
                                }
                            } else {
                                if (valeurActuelle <= valueMax) {
                                    tr.classList = "potentiel-regles success";
                                } else {
                                    tr.classList = "potentiel-regles danger";
                                    isValid = 0;
                                }
                            }
                        }
                    }
                    document.getElementById('PPvalid').classList.toggle('hidden', document.querySelectorAll('.potentiel-regles.danger').length || !document.querySelectorAll('.potentiel-regles.success').length);
                    document.getElementById('PPinvalid').classList.toggle('hidden', !document.querySelectorAll('.potentiel-regles.danger').length);
                });
            };

            changeAffectation = function (ligne, state) {
                if (! state) {
                    ligne.childNodes[17].innerText = '';
                    return ;
                }
                superficie = ligne.childNodes[13].childNodes[1].value;
                if (parseFloat(ligne.childNodes[11].innerText.replace(",", ".")) < parseFloat(superficie)) {
                    ligne.childNodes[17].innerText = 'Totale';
                    ligne.childNodes[13].childNodes[1].value = ligne.childNodes[11].innerText.replace(",", ".");
                } else if (parseFloat(ligne.childNodes[11].innerText.replace(",", ".")) == parseFloat(superficie)) {
                    ligne.childNodes[17].innerText = 'Totale';
                } else {
                    ligne.childNodes[17].innerText = 'Partielle';
                }
            };

            (document.querySelectorAll('table[id^=parcelles_] input') || []).forEach(function (el) {
                el.addEventListener('change', function(){
                    ligneActive = this.closest('tr');
                    ligneState = ligneActive.querySelector('input.bsswitch').checked;
                    changeAffectation(ligneActive, ligneState);
                    updateTotal(this.closest('table'));
                    updateRules()
                });
            });

            (document.querySelectorAll('table[id^=parcelles_]') || []).forEach(function (el) {
                updateTotal(el)
                updateRules()
            });

            $('.bsswitch').on('switchChange.bootstrapSwitch', function (event, state) {
                const table = event.target.closest('table')
                const ligneActive = event.target.closest('tr');
                changeAffectation(ligneActive, state);
                updateTotal(table)
                updateRules()
            });
        });

        document.querySelectorAll("a[class^=onglet-presentation]").forEach(function (el) {
            el.addEventListener("click", () => {
                let form = document.querySelector("#" + el.dataset.form);
                let input = document.createElement("input");
                input.setAttribute("type", "hidden");
                input.setAttribute("name", "service");
                input.setAttribute("value", el.dataset.href.substring(el.dataset.href.indexOf("destinataire")));
                form.append(input);
                form.submit();
            });
        });

    </script>

    <div class="row row-margin row-button"  style="display:flex; justify-content: space-evenly;">
        <div class="col-xs-4"><button type="submit" name="previous" value="1" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</button>
        </div>

        <div class="col-xs-4" style="display:flex; justify-content:center;"> <button type="submit" name="saveandquit" value="1" class="btn btn-default">Enregistrer en brouillon</button>
        </div>

        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php endif; ?>
