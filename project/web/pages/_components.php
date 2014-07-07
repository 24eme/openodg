<?php require_once('../config/inc.php'); ?>
<?php 
	$template = 1;
	$cat_current = "wysiwyg";
	$cat_title = "Composants";
	$page_title = "Composants";
?>
<?php require(INCLUDES_PATH.'_header.php'); ?>


<!-- #content -->
<section id="content" class="container">

	<h1>Titres</h1>

	<h1>Titre h1</h1>
	<h2>Titre h2</h2>
	<h2 class="h2-border">Titre h2 avec bordure</h2>
	<h3>Titre h3</h3>

	<h1>Boutons</h1>

	<div class="row-margin">
		<a href="#" class="btn btn-default">Bouton default</a>
		<a href="#" class="btn btn-primary">Bouton primary</a>
	</div>

	<div class="row-margin">
		<a href="#" class="btn btn-primary btn-prev">Bouton étape précédente</a>
		<a href="#" class="btn btn-primary btn-next">Bouton étape suivante</a>
	</div>

	<div class="row-margin">
		<a href="#" class="btn btn-primary btn-lg btn-prev">Bouton étape précédente</a>
		<a href="#" class="btn btn-primary btn-lg btn-next">Bouton étape suivante</a>
	</div>

	<div class="row-margin">
		<a href="#" class="btn btn-default btn-prev">Bouton étape précédente</a>
		<a href="#" class="btn btn-default btn-next">Bouton étape suivante</a>
	</div>

	<div class="row-margin">
		<a href="#" class="btn btn-default btn-lg btn-prev">Bouton étape précédente</a>
		<a href="#" class="btn btn-default btn-lg btn-next">Bouton étape suivante</a>
	</div>

	<div class="row-margin">
		<a href="#" class="btn btn-default btn-plus">Bouton plus</a>
		<a href="#" class="btn btn-default btn-plus">Bouton plus</a>
	</div>

	<div class="row-margin">
		<a href="#" class="btn btn-default btn-lg btn-plus">Bouton plus</a>
		<a href="#" class="btn btn-default btn-lg btn-plus">Bouton plus</a>
	</div>
	
	<h1>Tableaux</h1>

	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th>Lorem ipsum.</th>
				<th>Doloribus, reiciendis.</th>
				<th>Explicabo, odit!</th>
				<th>Autem, reprehenderit!</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Lorem ipsum.</td>
				<td>Officia, ullam.</td>
				<td>Maxime, in.</td>
				<td>Minima, delectus.</td>
			</tr>
			<tr>
				<td>Lorem ipsum.</td>
				<td>Officia, ullam.</td>
				<td>Maxime, in.</td>
				<td>Minima, delectus.</td>
			</tr>
			<tr>
				<td>Lorem ipsum.</td>
				<td>Officia, ullam.</td>
				<td>Maxime, in.</td>
				<td>Minima, delectus.</td>
			</tr>
		</tbody>
	</table>

	<h1>Rail d'étapes</h1>

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

	<h1>Onglets</h1>

	<ul class="nav nav-tabs" role="tablist">
		<li class="active"><a href="#tab-1" role="tab" data-toggle="tab">Lorem ipsum.</a></li>
		<li><a href="#tab-2" data-toggle="tab">Officia, officiis.</a></li>
	</ul>

	<div class="tab-content">
		<div id="tab-1" class="tab-pane active">
			<h2>Panels</h2>

			<div class="row">
				<div class="col-xs-4">
					<div class="panel panel-default">
		  				<div class="panel-heading">
		  					<h2 class="panel-title">Panel title</h2>
		  				</div>
						<div class="panel-body">
							Panel content
						</div>
					</div>
				</div>
				<div class="col-xs-4">
					<div class="panel panel-success">
		  				<div class="panel-heading">
		  					<h2 class="panel-title">Panel title</h2>
		  				</div>
						<div class="panel-body">
							Panel content
						</div>
					</div>
				</div>
				<div class="col-xs-4">
					<div class="panel panel-info">
		  				<div class="panel-heading">
		  					<h2 class="panel-title">Panel title</h2>
		  				</div>
						<div class="panel-body">
							Panel content
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="tab-2" class="tab-pane">
			Officia
		</div>
	</div>

	<h1>Alertes</h1>

	<div class="alert alert-danger" role="alert">
		<ul>
			<li>Lorem ipsum dolor sit. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
			<li>Quos quo sit laborum. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
		</ul>
	</div>

	<div class="alert alert-warning" role="alert">
		<ul>
			<li>Lorem ipsum dolor sit. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
			<li>Quos quo sit laborum. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
		</ul>
	</div>

	<div class="alert alert-success" role="alert">
		<ul>
			<li>Lorem ipsum dolor sit. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
			<li>Quos quo sit laborum. <a href="#" class="alert-link">Lorem ipsum dolor.</a></li>
		</ul>
	</div>


</section>
<!-- end #content -->

<?php require(INCLUDES_PATH.'_footer.php'); ?>