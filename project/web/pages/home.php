<?php require_once('../config/inc.php'); ?>
<?php 
	$template = 1;
	$cat_current = "home";
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
						S'il s'agit de votre premiere connexion, munissez vous de votre numéro CVI et du code à 4 chiffres de création reçus par courrier.
					</p>
					
					<div class="row">
						<div class="col-xs-7 col-xs-offset-5">
							<a href="#" class="btn btn-default btn-block">Créer votre compte</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-6 space-member">
			<div class="row">
				<div class="col-xs-10 col-xs-offset-1">
					<h2 class="h3">Espace adhérent</h2>

					<p>Entrez votre identifiant et votre mot de passe :</p>
					
					<form class="form-horizontal" action="#" method="post" role="form">
						<div class="form-group">
							<label class="col-xs-4 control-label" for="login">Identifiant :</label>
							<div class="col-xs-8">
								<input id="login" class="form-control" type="text" />
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-xs-4 control-label" for="password">Mot de passe :</label>
							<div class="col-xs-8">
								<input id="password" class="form-control" type="text" />
							</div>
						</div>
					
						<div class="form-group">
							<div class="col-xs-offset-4 col-xs-8">
								<a href="#" class="forgotten-password">Mot de passe oublié</a>
							</div>
						</div>
					
						<div class="form-group">
							<div class="col-xs-offset-8 col-xs-4">
								<button class="btn btn-default btn-block" type="submit">Valider</button>
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
			<div class="col-xs-4">
				<div class="module module-declarations">
					<h3>Un module de saisie des déclarations</h3>
					
					<ul>
						<li>Déclaration de revendication</li>
						<li>Déclaration d'identification</li>
						<li>Déclaration d'affectation parcellaire</li>
					</ul>
				</div>
			</div>
			<div class="col-xs-4">
				<div class="module module-bibliotheque">
					<h3>Une bibliothèque de documents téléchargables</h3>
					
					<ul>
						<li>Cahiers des Charges</li>
						<li>Accès aux Revues et Comptes Rendus</li>
						<li>Archives &amp; Recherche Avancée</li>
					</ul>
				</div>
			</div>
			<div class="col-xs-4">
				<div class="module module-cartographique">
					<h3>Un module de consultation cartographique</h3>
					
					<ul>
						<li>Cordonnée Cadastrales</li>
						<li>Recherche de Parcelles</li>
						<li>Cépages Autorisés</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

</section>
<!-- end #content -->

<?php require(INCLUDES_PATH.'_footer.php'); ?>