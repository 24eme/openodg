<?php use_helper('Date') ?>

<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<?php foreach ($parcellaireAffectation->declaration->getParcellesByDgc() as $dgc => $parcelles): ?>
<div style="margin-bottom: 1em;" class="row">
    <div class="col-xs-12">
        <h3>Dénomination complémentaire <?php echo str_replace("-", " ", $dgc); ?></h3>
    </div>
</div>
<table id="parcelles_<?php echo $commune; ?>" class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
	<thead>
    	<tr>
    		<th class="col-xs-2">Commune</th>
            <th class="col-xs-2">Lieu-dit</th>
            <th class="col-xs-1">Section /<br />N° parcelle</th>
            <th class="col-xs-2">Cépage</th>
            <th class="col-xs-1">Année plantat°</th>
            <th class="col-xs-1" style="text-align: right;">Surf. totale <span class="text-muted small">(ha)</span></th>
            <th class="col-xs-1" style="text-align: right;">Surf. dédiée&nbsp;<span class="text-muted small">(ha)</span></th>
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
            <td><?php echo $parcelle->campagne_plantation; ?></td>
            <td style="text-align: right;"><?php echo number_format($parcelle->superficie,4); ?></td>
            <td>
                <span  class="text-muted pull-left"><?php $percent = 100*($parcelle->superficie_affectation / $parcelle->superficie); echo floor($percent)."%"; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <span class="pull-right superficie2compute"><?php echo  number_format($parcelle->superficie_affectation,4); ?></span>
            </td>
        	<td class="text-center">
            	<div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['affectee']->hasError()): ?>has-error<?php endif; ?>">
                	<?php echo $form[$produitKey][$parcelle->getKey()]['affectee']->renderError() ?>
                    <div class="col-xs-12">
		            	<?php echo $form[$produitKey][$parcelle->getKey()]['affectee']->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                    </div>
                </div>
        	</td>
            <td style="text-align: center;">
                <?php if (round($parcelle->superficie_affectation,4) != round($parcelle->superficie,4)): ?>
                    <span>Partielle</span>
                <?php else: ?><span>Totale</span>
            <?php endif; ?>
            </td>
        </tr>
    <?php  endif; endforeach; ?>
    </tbody>
    <tfoot>
        <td colspan="6" class="text-right">Superficie totale :</td>
        <td colspan="" class="total_superficie text-right"></td>
        <td colspan="3" class="text-left"></td>
    </tfoot>
</table>
<?php endforeach; ?>