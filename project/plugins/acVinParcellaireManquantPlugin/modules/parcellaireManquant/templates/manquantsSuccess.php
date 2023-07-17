<?php include_partial('parcellaireManquant/breadcrumb', array('parcellaireManquant' => $parcellaireManquant)); ?>

<?php include_partial('parcellaireManquant/step', array('step' => 'manquants', 'parcellaireManquant' => $parcellaireManquant)) ?>
<div class="page-header">
    <h2>Pieds morts ou manquants sur votre exploitation <br/><small>Merci d'indiquer la densité et le % de pied manquant</small></h2>
    <div class="alert alert-info">
        <div style="display: inline-block; margin-right: 1rem;">
            <p><span class="glyphicon glyphicon-info-sign"></span></p>
        </div>
        <div style="display: inline-block; vertical-align: middle">
            Il n'est pas nécessaire d'indiquer les parcelles avec moins de 20% de pieds manquants.<br/>Si vous n'avez aucune parcelle concernée, vous pouvez aller directement à la <a href="<?php echo url_for('parcellairemanquant_validation', $parcellaireManquant) ?>">validation</a>.
        </div>
    </div>
</div>

<form action="<?php echo url_for("parcellairemanquant_manquants", $parcellaireManquant) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php foreach ($parcellaireManquant->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
	<h3><?php echo $commune; ?></h3>
    <table class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
		<thead>
        	<tr>
                <th class="col-xs-2">Lieu-dit</th>
                <th class="col-xs-1">Section /<br />N° parcelle</th>
                <th class="col-xs-3">Produit</th>
                <th class="col-xs-1">Année plantat°</th>
                <th class="col-xs-1" style="text-align: right;">Surf. <span class="text-muted small">(ha)</span></th>
                <th class="col-xs-1">Densité</th>
                <th class="col-xs-1">% de pieds manquants <span class="text-muted">(si&nbsp;+&nbsp;de&nbsp;20%)</span></th>
            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($parcelles as $parcelle):
                $produitKey = str_replace('/declaration/', '', $parcelle->getProduit()->getHash());
			if (isset($form[$produitKey][$parcelle->getKey()])):
		?>
			<tr class="vertical-center" id="tr_<?php echo str_replace("/","-",$produitKey)."-".$parcelle->getKey();?>">
                <td><?php echo $parcelle->lieu; ?></td>
                <td style="text-align: center;"><?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?></td>
                <td><span class="text-muted"><?php echo $parcelle->getProduitLibelle(); ?></span> <?php echo $parcelle->cepage; ?></td>
                <td><?php echo $parcelle->campagne_plantation; ?></td>
                <td style="text-align: right;"><?php echo $parcelle->superficie; ?></td>
            	<td>
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['densite']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$produitKey][$parcelle->getKey()]['densite']->renderError() ?>
                        <div class="col-xs-12">
                        	<?php echo $form[$produitKey][$parcelle->getKey()]['densite']->render(array('class' => 'form-control', "placeholder" => "Densité")) ?>
                        </div>
                    </div>
            	</td>
            	<td>
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['pourcentage']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$produitKey][$parcelle->getKey()]['pourcentage']->renderError() ?>
                        <div class="col-xs-12">
                        	<?php echo $form[$produitKey][$parcelle->getKey()]['pourcentage']->render(array('class' => 'form-control', "placeholder" => "% de pieds manquants")) ?>
                        </div>
                    </div>
            	</td>
            </tr>
        <?php  endif; endforeach; ?>
        </tbody>
	</table>
    <?php  endforeach; ?>
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellairemanquant_parcelles", $parcellaireManquant); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-4 text-center">
            <button type="submit" name="saveandquit" value="1" class="btn btn-default">Enregistrer en brouillon</button>
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
