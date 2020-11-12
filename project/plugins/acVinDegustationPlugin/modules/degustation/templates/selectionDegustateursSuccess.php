<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_DEGUSTATEURS)); ?>


<div class="page-header no-border">
    <h2>Sélection des dégustateurs</h2>
</div>
<div class="alert alert-info" role="alert">
  <h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?></h3>
  <h4>Lieu : <strong><?php echo $degustation->getLieuNom(); ?></strong></h4>
  <table class="table table-condensed">
    <tbody>
      <?php foreach (DegustationConfiguration::getInstance()->getColleges() as $tag => $libelle): ?>
      <tr class="vertical-center" data-hash="<?php echo $infosDegustation["degustateurs"][$libelle]['key']; ?>" >
        <td class="col-xs-4" >Nombre de <strong><?php echo $libelle; ?>&nbsp;:</strong></td>
        <td class="col-xs-8"><strong class="<?php echo $infosDegustation["degustateurs"][$libelle]['key']; ?>" ><?php echo $infosDegustation["degustateurs"][$libelle]['total']; ?></strong></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p>Sélectionnez l'ensemble des dégustateurs en vue de leurs participations à la dégustation</p>

<div class="form-group">
  <input id="hamzastyle" type="hidden" data-placeholder="Sélectionner un nom :" data-hamzastyle-container=".table_college" data-hamzastyle-mininput="3" class="select2autocomplete hamzastyle form-control">
</div>

<form action="<?php echo url_for("degustation_selection_degustateurs", $degustation) ?>" method="post" class="form-horizontal degustation degustateurs">
	<?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>
	<?php foreach ($form['degustateurs'] as $college => $collegeForm): ?>
    <?php $collegeName = DegustationConfiguration::getInstance()->getLibelleCollege($college); ?>

	<h3><?php echo $collegeName; ?></h3>
    <table id="table_college_<?=$college?>" class="table table-bordered table-condensed table-striped table_college">
		<thead>
        	<tr>
        		<th class="col-xs-11">Membre</th>
                <th class="col-xs-1">Sélectionner?</th>

            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($collegeForm as $idCompte => $compteForm):
			$compte = $form->getCompteByCollegeAndIdentifiant($college, $idCompte);
      $words = json_encode(array_merge(
        explode(' ', strtolower($compte->getNomAAfficher())), explode(' ', $compte->getAdresse()), explode(' ', $compte->getAdresseComplementaire()),
        [$compte->getCommune(), $compte->getCodePostal(), $compte->identifiant]
      ), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		?>
            <tr class="vertical-center cursor-pointer hamzastyle-item" data-words='<?= $words ?>'>
                <td><?php echo $compte->getLibelleWithAdresse() ?></td>
                <td class="text-center" data-hash="<?php echo $infosDegustation["degustateurs"][$collegeName]['key']; ?>">
                	<div style="margin-bottom: 0;" class="form-group <?php if($compteForm['selectionne']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $compteForm['selectionne']->renderError() ?>
                        <div class="col-xs-12">
			            	<?php echo $compteForm['selectionne']->render(array('class' => "bsswitch ajax", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                    </div>
            	</td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
	</table>
	<?php endforeach; ?>

	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation_prelevement_lots", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>
<?php use_javascript('hamza_style.js'); ?>
