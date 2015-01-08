<div class="page-header">
    <h2>Recherche de compte</h2>
</div>
<form action="<?php echo url_for("compte_recherche") ?>" method="get" class="form-horizontal">
	<div class="row">
		<div class="col-xs-12">
			<?php echo $form->renderHiddenFields(); ?>
		    <?php echo $form->renderGlobalErrors(); ?>
			<div class="input-group">
				<?php echo $form["q"]->render(array("value" => ($q == '*')? '' : $q, "class" => "form-control input-lg", "placeholder" => "Votre recherche...")); ?>
				<span class="input-group-btn">
		        	<button class="btn btn-lg btn-info" type="submit" style="font-size: 22px; padding-top: 8px; padding-bottom: 8px;"><span class="glyphicon glyphicon-search"></span></button>
		      	</span>
			</div>
			<div class="input-group">
				<div class="checkbox">
				    <label>
				      	<?php echo ($all)? $form["all"]->render(array('checked' => 'checked')) : $form["all"]->render(); ?> Inclure les comptes inactifs
				    </label>
				</div>
			</div>
		</div>
		<div class="col-xs-9"></div>
		<div class="col-xs-3 ">
			<p class="text-right">
			<?php 
				$argsTemplate = $args->getRawValue();
				foreach ($argsTemplate['tags'] as $key => $tag):
					$argsTemplate['tags'] = array_diff($argsTemplate['tags'], array($tag));
					$explodeTag = explode(':', $tag);
			?>
				<a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class="btn btn-info btn-xs"><?php echo $explodeTag[1] ?> <span class="glyphicon glyphicon-remove"></span></a>
			<?php $argsTemplate = $args->getRawValue(); endforeach; ?>
			</p>
		</div>
	</div>
</form>

<div class="col-xs-12">
	<p><?php echo $nb_results ?> résultat<?php if ($nb_results > 1): ?>s<?php endif; ?></p>
</div>
<?php if($nb_results > 0): ?>
<div class="row">
	<div class="col-xs-9">
		<table class="table table-striped table-hover">
			<?php 
			foreach($results as $res): 
			$data = $res->getData();
			?>
			<tr>
				<td>
					<strong><?php echo $data['nom_a_afficher']; ?></strong><br />
					<?php echo $data['email']; ?>
				</td>
				<td>
					<?php echo $data['adresse']; ?><br />
					<?php echo $data['code_postal']; ?>&nbsp;<?php echo $data['commune']; ?>
				</td>
				<td>
					Bureau : <?php echo $data['telephone_bureau']; ?><br />
					Fax : <?php echo $data['fax']; ?><br />
					Mobile : <?php echo $data['telephone_mobile'] ?><br />
					Privé : <?php echo $data['telephone_prive']; ?>
				</td>
				<td>
					<?php 
						$i=0; 
						$nbAttributs = count($data['tags']['attributs']);
						foreach ($data['tags']['attributs'] as $attributValue) {
							$i++;
							echo $attributValue;
							if ($i != $nbAttributs) {
								echo "<br />";
							}
						}
					?>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
	<div class="col-xs-3">
		<?php foreach($facets as $type => $ftype): ?>
		<div class="panel panel-default" style="margin-bottom: 10px;">
			<div class="panel-heading" style="padding-top: 0px; margin: 0 5px;"><?php echo ucfirst($type) ?></div>
			<div class="panel-body" style="margin: 0 5px; padding: 10px 0;">
				<?php if (count($ftype['terms'])): ?>
				<div class="list-group">
					<?php 
						foreach($ftype['terms'] as $f): 
						$tag = $type.':'.$f['term'];
						$argsTemplate = $args->getRawValue();
						if (!in_array($tag, $argsTemplate['tags'])) {
							$argsTemplate['tags'][] = $tag;
						}
					?>
					<a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class="list-group-item"><span class="badge"><?php echo $f['count'] ?></span><?php echo $f['term'] ?></a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>
<?php if ($nb_results > 0 && $last_page > 1): ?>
<div class="row">
	<div class="col-xs-9 text-center">
		<nav>
			<ul class="pagination">
			<?php 
				$args = array('q' => $q, 'tags' => $args['tags']); 
			?>
			<?php if ($current_page > 1) : ?>
				<li><a href="<?php echo url_for('compte_recherche', $args); ?>" aria-label="Previous"><span aria-hidden="true">&laquo;&laquo;</span></a></li>
				<?php if ($current_page > 1) $args['page'] = $current_page - 1; ?>
				<li><a href="<?php echo url_for('compte_recherche', $args); ?>" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
			<?php else: ?>
				<li class="disabled"><span aria-hidden="true">&laquo;&laquo;</span></li>
				<li class="disabled"><span aria-hidden="true">&laquo;</span></li>
			<?php endif; ?>
			<li><span aria-hidden="true"><?php echo $current_page ?>/<?php echo $last_page ?></span></li>
			<?php if ($current_page < $last_page) $args['page'] = $current_page + 1; else $args['page'] = $last_page ;?>
			<?php if ($current_page != $args['page']): ?>
				<li><a href="<?php echo url_for('compte_recherche', $args); ?>" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
			<?php else: ?>
				<li class="disabled"><span aria-hidden="true">&raquo;</span></li>
			<?php endif; ?>
			<?php $args['page'] = $last_page; ?>
			<?php if ($current_page != $args['page']): ?>
            	<li><a href="<?php echo url_for('compte_recherche', $args); ?>" aria-label="Next"><span aria-hidden="true">&raquo;&raquo;</span></a></li>
			<?php else: ?>
				<li class="disabled"><span aria-hidden="true">&raquo;&raquo;</span></li>
			<?php endif; ?>
			</ul>
		</nav>
	</div>
	<div class="col-xs-3"></div>
</div>
<?php endif; ?>