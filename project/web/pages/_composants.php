<?php require_once('../config/inc.php'); ?>
<?php 
	$template = 1;
	$cat_current = "wysiwyg";
	$cat_title = "Page vide";
	$page_title = "Page vide";
?>
<?php require(INCLUDES_PATH.'_header.php'); ?>


<!-- #content -->
<section id="content" class="container">

	<h1>Grille</h1>

	<div class="row">
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
		<div class="col-md-1" style="height:40px;border:1px solid #000;">.col-md-1</div>
	</div>

	<h1>Titres</h1>

	<h1>Titre h1</h1>
	<h2>Titre h2</h2>
	<h3>Titre h3</h3>

	<h1>Boutons</h1>

	<a href="#" class="btn btn-default">Bouton default</a>
	<a href="#" class="btn btn-step">Bouton étape</a>

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
				<a href="#">Exploitation</a>
			</div>
		</li>
		<li class="active">
			<div class="step">
				<a href="#">Lots</a>
			</div>
		</li>
		<li class="visited">
			<div class="step">
				<a href="#">Dégustation</a>
			</div>
		</li>
		<li>
			<div class="step">
				<a href="#">Validation</a>
			</div>
		</li>
	</ol>

</section>
<!-- end #content -->

<?php require(INCLUDES_PATH.'_footer.php'); ?>