<?php require_once('../config/inc.php'); ?>
<?php 
	$template = 1;
	$cat_current = "wysiwyg";
	$cat_title = "Contrôle externe";
	$page_title = "Contrôle externe";
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
		<li class="active">
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

	<ul class="nav nav-tabs" role="tablist">
		<li class="active">
			<a href="#" role="tab">Prélèvement en bouteille</a>
		</li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active">
			<form class="form-horizontal">
				<div class="row">
					<div class="col-xs-7">
						<p>Vin prêt à être dégusté ou plus proche de la commercialisation...</p>
						
						<h2 class="h2-border">Aoc Alsace</h2>
						
						<p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
				
						<div class="form-group">
							<label class="col-xs-4 control-label">Semaine du </label>
							<div class="col-xs-8">
								<div id="date-picker-1" class="input-group date-picker">
									<input class="form-control" type="text" />
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</div>
								</div>
							</div>
						</div>

						<h2 class="h2-border">AOC Alsace Grand Cru</h2>

						<p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
				
						<div class="form-group">
							<label class="col-xs-4 control-label">Semaine du </label>
							<div class="col-xs-8">
								<div id="date-picker-1" class="input-group date-picker">
									<input class="form-control" type="text" />
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</div>
								</div>
							</div>
						</div>

						<h2 class="h2-border">VT / SGN</h2>

						<p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
				
						<div class="form-group">
							<label class="col-xs-4 control-label">Semaine du </label>
							<div class="col-xs-8">
								<div id="date-picker-1" class="input-group date-picker">
									<input class="form-control" type="text" />
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</div>
								</div>
							</div>
						</div>

						<p>Nombre total de lots VT / SGN (toutes appellations confondues) : 3</p>
					</div>
					<div class="col-xs-4 col-xs-offset-1">
						<h2>Lieu de prélèvement :</h2>
										
						<span>Nom du responsable : Gwenael Chichery</span> <br />
						<span>Adresse : 1, rue Garnier Neuilly, 92110</span> <br />
						<span>Tél : 06 82 87 68 92</span><br />
										
						<div class="row-margin text-right">
							<a href="#" class="btn btn-default">Modifier</a>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="row row-margin">
		<div class="col-xs-4"><a href="#" class="btn btn-primary btn-lg btn-block btn-prev"><span class="eleganticon arrow_carrot-left"></span> étape précendente</a></div>
		<div class="col-xs-4 col-xs-offset-4"><a href="#" class="btn btn-primary btn-lg btn-block btn-next">étape suivante <span class="eleganticon arrow_carrot-right"></span></a></div>
	</div>
	
</section>
<!-- end #content -->

<?php require(INCLUDES_PATH.'_footer.php'); ?>