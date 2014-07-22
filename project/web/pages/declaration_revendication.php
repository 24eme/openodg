<?php require_once('../config/inc.php'); ?>
<?php 
	$template = 1;
	$cat_current = "wysiwyg";
	$cat_title = "Revendication";
	$page_title = "Revendication";
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
		<li class="active">
			<div class="step">
				<a href="#">Revendication</a>
			</div>
		</li>
		<li>
			<div class="step">
				<a href="#">Dégustation conseil</a>
			</div>
		</li>
		<li>
			<div class="step">
				<a href="#">Contrôle externe</a>
			</div>
		</li>
		<li>
			<div class="step">
				<a href="#">Validation</a>
			</div>
		</li>
	</ol>

	<div class="frame">
			
		<p>Veuillez saisir les informations des AOC revendiquées dans la déclaration de récolte de l'année</p>

		<div class="row">
			<div class="col-xs-3 col-xs-offset-9 text-center">
				<span class="label label-primary">Informations issues de la DR</span>
			</div>
		</div>

		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th class="col-xs-5 small">Appellation revendiquée</th>
					<th class="col-xs-2 small">Superficie totale (ares)</th>
					<th class="col-xs-2 small">Volume revendiqué (hl)</th>
					<th class="col-xs-1 table-split small">Volume sur place (hl)</th>
					<th class="col-xs-1 small">Volume total (hl)</th>
					<th class="col-xs-1 small">Usages industriels</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="col-xs-5">AOC Alsace Blanc (VT/SGN inclus)</td>
					<td class="col-xs-2">
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<input class="form-control input-sm input-rounded" type="text" />
							</div>
						</div>
					</td>
					<td class="col-xs-2">
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<input class="form-control input-sm input-rounded" type="text" />
							</div>
						</div>
					</td>
					<td class="col-xs-1 table-split">Minima, delectus.</td>
					<td class="col-xs-1">Lorem ipsum dolor.</td>
					<td class="col-xs-1">Lorem ipsum dolor.</td>
				</tr>
				<tr>
					<td class="col-xs-5">Lorem ipsum.</td>
					<td class="col-xs-2">
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<input class="form-control input-sm input-rounded" type="text" />
							</div>
						</div>
					</td>
					<td class="col-xs-2">
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<input class="form-control input-sm input-rounded" type="text" />
							</div>
						</div>
					</td>
					<td class="col-xs-1 table-split">Minima, delectus.</td>
					<td class="col-xs-1">Minima, delectus.</td>
					<td class="col-xs-1">Minima, delectus.</td>
				</tr>
				<tr>
					<td class="col-xs-5">Lorem ipsum.</td>
					<td class="col-xs-2">
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<input class="form-control input-sm input-rounded" type="text" />
							</div>
						</div>
					</td>
					<td class="col-xs-2">
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<input class="form-control input-sm input-rounded" type="text" />
							</div>
						</div>
					</td>
					<td class="col-xs-1 table-split">Minima, delectus.</td>
					<td class="col-xs-1">Minima, delectus.</td>
					<td class="col-xs-1">Minima, delectus.</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="row row-margin">
		<div class="col-xs-4"><a href="#" class="btn btn-primary btn-lg btn-block btn-prev"><span class="eleganticon arrow_carrot-left"></span> étape précendente</a></div>
		<div class="col-xs-4 col-xs-offset-4"><a href="#" class="btn btn-primary btn-lg btn-block btn-next">étape suivante <span class="eleganticon arrow_carrot-right"></span></a></div>
	</div>
	
</section>
<!-- end #content -->

<?php require(INCLUDES_PATH.'_footer.php'); ?>