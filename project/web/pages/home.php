<?php require_once('../config/inc.php'); ?>
<?php 
	$template = 1;
	$cat_current = "home_auth";
	$cat_title = "Accueil";
	$page_title = "Accueil";
?>
<?php require(INCLUDES_PATH.'_header.php'); ?>


<!-- #content -->
<section id="content" class="container">

	<div class="row">
		<div class="col-xs-6 first-connection">
			<div class="row">
				<div class="col-xs-10 col-xs-offset-1">
					
					<h2 class="h3">Première connexion</h2>
								
					<p>
						S'il s'agit de votre première connexion, munissez-vous de votre numéro CVI et du code de création à 4 chiffres que vous avez reçu par courrier.
					</p>
					
					<div class="row">
						<div class="col-xs-7 col-xs-offset-5">
							<a href="#" class="btn btn-default btn-block btn-lg">Créer votre compte</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-6 space-member">
			<div class="row">
				<div class="col-xs-10 col-xs-offset-1">
					<h2 class="h3">Espace Adhérent</h2>

					<p>Entrez votre identifiant et votre mot de passe :</p>
					
					<form class="form-horizontal" action="#" method="post" role="form">
						<div class="form-group form-group-lg">
							<label class="col-xs-4 control-label" for="login">Identifiant :</label>
							<div class="col-xs-8">
								<input id="login" placeholder="Votre N° CVI" class="form-control" type="text" />
							</div>
						</div>
					
						<div class="form-group form-group-lg">
							<label class="col-xs-4 control-label" for="password">Le mot de passe</label>
							<div class="col-xs-8">
								<input id="password" placeholder="Le même que sur Vinsalsace.pro" class="form-control" type="text" />
							</div>
						</div>
					
						<div class="form-group">
							<div class="col-xs-offset-4 col-xs-8">
								<a href="#" class="forgotten-password">Mot de passe oublié</a>
							</div>
						</div>
					
						<div class="form-group">
							<div class="col-xs-offset-8 col-xs-4">
								<button class="btn btn-default btn-block btn-lg" type="submit">Valider</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>


	<div class="presentation-services">
		<div class="row">
			<div class="col-xs-12 presentation-title">
				<h2>Sur ce site, l'AVA met à la disposition de ses adhérents une multiplicité de services :</h2>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-6">
				<div class="module module-declarations">
					<h3>Un module <br />de saisie des déclarations</h3>
					
					<ul>
						<li>Déclaration <br />de Revendication</li>
						<li>
							<span style="opacity: 0.4;">Déclaration <br />d'Affectation Parcellaire</span><br />
							<span class="text-primary">MARS 2015</span>
						</li>
						<li>
							<span style="opacity: 0.4;">Déclaration d'Identification</span><br />
							<span class="text-primary">PROCHAINEMENT</span>
						</li>
					</ul>
				</div>
			</div>
			<div class="col-xs-6" style="position:relative;">
				<div style="opacity: 0.4;" class="module module-bibliotheque">
					<h3>Une bibliothèque de documents téléchargables</h3>
					
					<ul>
						<li>Cahiers des Charges</li>
						<li>Archives de la revue<br />des Vins d'Alsace</li>
						<li>Archives</li>
						<li>Recherche Avancée</li>
					</ul>
				</div>
				<h3 style="position: absolute; top: 34px; left: 153px; 	-ms-transform: rotate(-30deg); -webkit-transform: rotate(-30deg); transform: rotate(-30deg);"><strong>Prochainement !</strong></h3>
			</div>
		</div>
	</div>

</section>
<!-- end #content -->

<?php require(INCLUDES_PATH.'_footer.php'); ?>