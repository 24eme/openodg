<?php use_helper('Float') ?>
<?php use_helper('Date') ?>

<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<?php foreach ($parcellaireAffectation->declaration->getGroupedParcelles() as $group => $parcelles): ?>
<?php if ($group): ?>
    <div style="margin-bottom: 1em;" class="row">
        <div class="col-xs-6">
            <h3><?php if ($parcellaireAffectation->declaration->isDgcGroup): ?>Dénomination complémentaire <?php endif; ?><?php echo str_replace("-", " ", $group); ?></h3>
        </div>
        <div class="col-xs-6">
           <p class="text-right" style="margin-top: 20px;"><a href="javascript:void(0)" class="bootstrap-switch-activeall" data-target="#parcelles_<?php echo $group; ?>" style="display: none;"><span class='glyphicon glyphicon-check'></span>&nbsp;Toutes les parcelles de cette <?php if ($parcellaireAffectation->declaration->isDgcGroup): ?>dénomination<?php else: ?>commune<?php endif; ?></a><a href="javascript:void(0)" class="bootstrap-switch-removeall" data-target="#parcelles_<?php echo $group; ?>" style="display: none;"><span class='glyphicon glyphicon-remove'></span>&nbsp;Désélectionner toutes les parcelles de cette  <?php if ($parcellaireAffectation->declaration->isDgcGroup): ?>dénomination<?php else: ?>commune<?php endif; ?></a></p>
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
  $parcelles = $parcelles->getRawValue();
  ksort($parcelles);
		foreach ($parcelles as $parcelle):
            $produitKey = str_replace('/declaration/', '', $parcelle->getProduit()->getHash());
		if (isset($form[$produitKey][$parcelle->getKey()])):
	?>
		<tr class="vertical-center" id="tr_<?php echo str_replace("/","-",$produitKey)."-".$parcelle->getKey();?>">
			<td><?php echo $parcelle->commune; ?></td>
            <td><?php echo $parcelle->lieu; ?></td>
            <td style="text-align: center;"><?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?></td>
            <td><?php echo $parcelle->cepage; ?></td>
            <td class="text-center"><?php echo $parcelle->campagne_plantation; ?></td>
            <td class="text-right"><?php echoFloatFr($parcelle->superficie,4); ?></td>
            <td class="text-right">
                <!-- <span  class="text-muted pull-left"><?php $percent = 100*($parcelle->superficie_affectation / $parcelle->superficie); echo floor($percent)."%"; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> -->
                <?php echo $form[$produitKey][$parcelle->getKey()]['superficie_affectation']->render(); ?>
            </td>
        	<td class="text-center">
            	<div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['affectee']->hasError()): ?>has-error<?php endif; ?>">
                	<?php echo $form[$produitKey][$parcelle->getKey()]['affectee']->renderError() ?>
                    <div class="col-xs-12">
		            	<?php echo $form[$produitKey][$parcelle->getKey()]['affectee']->render(array('class' => "bsswitch test", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                    </div>
                </div>
        	</td>
            <td class="text-center">/
                <?php if (round($parcelle->superficie_affectation,4) < round($parcelle->superficie,4)): ?>
                    <span>Partielle</span>
                <?php else: ?><span>Totale</span>
            <?php endif; ?>
            </td>
        </tr>
    <?php  endif; endforeach; ?>
    </tbody>
</table>
<?php endforeach; ?>
