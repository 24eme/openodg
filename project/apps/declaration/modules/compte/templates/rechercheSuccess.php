<div class="page-header">
    <h2>Recherche de compte</h2>
</div>
<form action="<?php echo url_for("compte_recherche") ?>" method="get" class="form-horizontal">
	<div class="row">
		<div class="col-xs-12">
			<?php echo $form->renderHiddenFields(); ?>
		    <?php echo $form->renderGlobalErrors(); ?>
			<div class="input-group">
				<?php echo $form["query"]->render(array("class" => "form-control input-lg", "placeholder" => "Votre recherche...")); ?>
				<span class="input-group-btn">
		        	<button class="btn btn-lg btn-info" type="submit" style="font-size: 22px; padding-top: 8px; padding-bottom: 8px;"><span class="glyphicon glyphicon-search"></span></button>
		      	</span>
			</div>
		</div>
		<div class="col-xs-9"></div>
		<div class="col-xs-3 ">
			<p class="text-right">
				<button type="button" class="btn btn-info btn-xs">Dégustateur <span class="glyphicon glyphicon-remove"></span></button>
				<button type="button" class="btn btn-info btn-xs">AOC Alsace Blanc <span class="glyphicon glyphicon-remove"></span></button>
			</p>
		</div>
	</div>
</form>
<div class="col-xs-12">
	<p>10 résultats</p>
</div>
<div class="row">
	<div class="col-xs-9">
		<table class="table table-striped table-hover">
			<tr>
				<td>
					M Test Compte 1<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>Prélèvement</td>
			</tr>
			<tr>
				<td>
					M Test Compte 2<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>Dégustateur</td>
			</tr>
			<tr>
				<td>
					M Test Compte 3<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>
					Prélèvement<br />
					Dégustateur
				</td>
			</tr>
			<tr>
				<td>
					M Test Compte 4<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>
					Prélèvement<br />
					Dégustateur
				</td>
			</tr>
			<tr>
				<td>
					M Test Compte 5<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>
					Dégustateur
				</td>
			</tr>
			<tr>
				<td>
					M Test Compte 6<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>
					Prélèvement<br />
					Dégustateur
				</td>
			</tr>
			<tr>
				<td>
					M Test Compte 7<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>
					Prélèvement
				</td>
			</tr>
			<tr>
				<td>
					M Test Compte 8<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>
					Prélèvement<br />
					Dégustateur
				</td>
			</tr>
			<tr>
				<td>
					M Test Compte 9<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>
					Prélèvement<br />
					Dégustateur
				</td>
			</tr>
			<tr>
				<td>
					M Test Compte 10<br />
					contact@actualys.com
				</td>
				<td>
					1 rue Garnier<br />
					92200 Neuilly Sur Seine
				</td>
				<td>
					Bureau : 0171113190<br />
					Fax : 0141434803<br />
					Mobile : +33689285475<br />
					Privé :
				</td>
				<td>
					Prélèvement
				</td>
			</tr>
			
		</table>
	</div>
	<div class="col-xs-3">
		<div class="panel panel-default" style="margin-bottom: 10px;">
			<div class="panel-heading" style="padding-top: 0px; margin: 0 5px;">Attributs</div>
			<div class="panel-body" style="margin: 0 5px; padding: 10px 0;">
				<div class="list-group">
					<a href="#" class="list-group-item"><span class="badge">25</span>Dégustateur</a>
					<a href="#" class="list-group-item"><span class="badge">15</span>Préleveur</a>
				</div>
			</div>
		</div>
		<div class="panel panel-default" style="margin-bottom: 10px;">
			<div class="panel-heading" style="padding-top: 0px; margin: 0 5px;">Produits</div>
			<div class="panel-body" style="margin: 0 5px; padding: 10px 0;">
				<div class="list-group">
					<a href="#" class="list-group-item"><span class="badge">19</span>AOC Alsace blanc</a>
					<a href="#" class="list-group-item"><span class="badge">8</span>AOC Alsace Grands Crus</a>
					<a href="#" class="list-group-item"><span class="badge">24</span>AOC Crémant d'Alsace</a>
					<a href="#" class="list-group-item"><span class="badge">6</span>AOC Alsace Pinot Noir Rouge</a>
				</div>
			</div>
		</div>
		<div class="panel panel-default" style="margin-bottom: 10px;">
			<div class="panel-heading" style="padding-top: 0px; margin: 0 5px;">Tags</div>
			<div class="panel-body" style="margin: 0 5px; padding: 10px 0;">
				<div class="list-group">
					<a href="#" class="list-group-item"><span class="badge">5</span>Hôtel</a>
					<a href="#" class="list-group-item"><span class="badge">14</span>Restaurant</a>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xs-9 text-center">
		<nav>
			<ul class="pagination">
				<li><a href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
			    <li><a href="#">1</a></li>
			    <li><a href="#">2</a></li>
			    <li><a href="#">3</a></li>
			    <li><a href="#">4</a></li>
			    <li><a href="#">5</a></li>
			    <li><a href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
			</ul>
		</nav>
	</div>
	<div class="col-xs-3"></div>
</div>