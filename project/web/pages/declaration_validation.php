<?php require_once('../config/inc.php'); ?>
<?php 
	$template = 1;
	$cat_current = "wysiwyg";
	$cat_title = "Validation";
	$page_title = "Validation";
?>
<?php require(INCLUDES_PATH.'_header.php'); ?>


<!-- #content -->
<section id="content" class="container">

	<ol class="breadcrumb-steps">
		<li class="visited">
			<div class="step">
				<a href="#">Exploitation</a>
			</div>
		</li>
		<li class="visited">
			<div class="step">
				<a href="#">Revendication</a>
			</div>
		</li>
		<li class="visited">
			<div class="step">
				<a href="#">Dégustation conseil</a>
			</div>
		</li>
		<li class="visited">
			<div class="step">
				<a href="#">Contrôle externe</a>
			</div>
		</li>
		<li class="active">
			<div class="step">
				<a href="#">Validation</a>
			</div>
		</li>
	</ol>

	<div class="frame">
		<h2 class="h3">Points bloquants</h2>
		
		<div class="alert-container">
			<div class="alert alert-danger" role="alert">
				<ul>
					<li>Lorem ipsum dolor sit. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
					<li>Quos quo sit laborum. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
				</ul>
			</div>
		</div>

		<h2 class="h3">Points de vigilance</h2>
		
		<div class="alert-container">
			<div class="alert alert-warning" role="alert">
				<ul>
					<li>Lorem ipsum dolor sit. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
					<li>Quos quo sit laborum. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
				</ul>
			</div>
		</div>

		<h2 class="h2-border">Récapitulatif</h2>

		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th class="col-xs-6"></th>
					<th class="col-xs-2 text-center">Nom VT / SGN(lot)</th>
					<th class="col-xs-2 text-center">VT / SGN(lot)</th>
					<th class="col-xs-2 text-center">Total</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="col-xs-6">Riesling</td>
					<td class="col-xs-2">
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<input class="form-control input-sm input-rounded" type="text" />
							</div>
						</div>
					</td>
					<td class="col-xs-2"></td>
					<td class="col-xs-2"></td>
				</tr>
				<tr>
					<td class="col-xs-6">Pinot Gris</td>
					<td class="col-xs-2">
						<div class="col-xs-10 col-xs-offset-1">
							<input class="form-control input-sm input-rounded" type="text" />
						</div>
					</td>
					<td class="col-xs-2"></td>
					<td class="col-xs-2"></td>
				</tr>
				<tr>
					<td class="col-xs-6">Pinot Noir</td>
					<td class="col-xs-2">
						<div class="col-xs-10 col-xs-offset-1">
							<input class="form-control input-sm input-rounded" type="text" />
						</div>
					</td>
					<td class="col-xs-2"></td>
					<td class="col-xs-2"></td>
				</tr>
			</tbody>
		</table>

		<h2>Engagements</h2>

		<div class="alert-container">
			<div class="alert alert-success" role="alert">
				<strong>Je m'engage à :</strong>
			
				<div class="checkbox">
					<label>
						<input type="checkbox" />
						Joindre un copie de votre déclaration (automatique)
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" />
						Envoyer par voie postale le carnet de préssoir pour le Crémant d’Alsace avant le 15 février
					</label>
				</div>
			</div>
		</div>
	</div>

	<div class="row row-margin">
		<div class="col-xs-4">
			<a href="#" class="btn btn-primary btn-lg btn-block btn-prev">étape précendente</a>
		</div>
		<div class="col-xs-4 text-center">
			<a href="#" class="btn btn-default btn-lg">
				<span class="glyphicon glyphicon-save"></span>
				Prévisualiser
			</a>
		</div>
		<div class="col-xs-4">
			<a href="#" class="btn btn-primary btn-lg btn-block btn-next">valider</a>
		</div>
	</div>
	
</section>
<!-- end #content -->

<?php require(INCLUDES_PATH.'_footer.php'); ?>