<?php
$global_error_msg = "";
foreach ($form->getGlobalErrors() as $item):
    $global_error_msg = $item->getMessage();
    break;
endforeach;

$hasError = ($global_error_msg != "");
?>

<?php include_partial('tirage/step', array('step' => 'lots', 'tirage' => $tirage)) ?>

<div class="page-header no-border">
    <h2>Lots <small>Réalisée par l'ODG - AVA</small></h2>
</div>


<form method="post" action="<?php echo url_for('tirage_lots', $tirage) ?>" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>

    <?php if ($hasError): ?>
        <div class="alert alert-danger" role="alert"><?php echo $global_error_msg; ?></div>
    <?php endif; ?>
    <p>Veuillez indiquer le nombre de lots susceptibles d'être prélevés en AOC Alsace (<strong>AOC Alsace Communale et Lieu-dit inclus</strong>).</p>

    <p>Un lot doit correspondre au maximum à 4 récipients et au maximum à 2000 hl.</p>
    
    
    <div class="row">
    
    	<div class="col-xs-7">
    		<div class="row-margin">
				<div class="col-xs-offset-1">
					<p>
						Mise en bouteille : 
						<a class="btn-tooltip btn btn-lg" data-toggle="tooltip" data-placement="auto" title="" data-original-title="Les vins sont à présenter fermentation terminée, stabilisés et clarifiés (filtration non obligatoire)"><span class="glyphicon glyphicon-question-sign"></span></a>
					</p>
					<div class="form-group <?php if ($form["date_mise_en_bouteille_debut"]->hasError()): ?>has-error<?php endif; ?>">
						<?php if ($form["date_mise_en_bouteille_debut"]->hasError()): ?>                            
							<div class="alert alert-danger" role="alert"><?php echo $form["date_mise_en_bouteille_debut"]->getError(); ?></div>
                        <?php endif; ?> 
						<label class="col-xs-5 control-label">du</label>
						<div class="col-xs-7">
							<div class="input-group date-picker">
								<?php echo $form["date_mise_en_bouteille_debut"]->render(array("class" => "form-control")); ?>
                                <div class="input-group-addon">
                                	<span class="glyphicon glyphicon-calendar"></span>
                                </div>
							</div>
						</div>
					</div>
					<div class="form-group <?php if ($form["date_mise_en_bouteille_fin"]->hasError()): ?>has-error<?php endif; ?>">
						<?php if ($form["date_mise_en_bouteille_fin"]->hasError()): ?>                            
							<div class="alert alert-danger" role="alert"><?php echo $form["date_mise_en_bouteille_fin"]->getError(); ?></div>
                        <?php endif; ?>      
						<label class="col-xs-5 control-label">au</label>                      
						<div class="col-xs-7">
							<div class="input-group date-picker">
								<?php echo $form["date_mise_en_bouteille_fin"]->render(array("class" => "form-control")); ?>
                                <div class="input-group-addon">
                                	<span class="glyphicon glyphicon-calendar"></span>
                                </div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
    		<div class="row-margin">
				<div class="col-xs-offset-1">
					<p>
						Composé de : 
						<a class="btn-tooltip btn btn-lg" data-toggle="tooltip" data-placement="auto" title="" data-original-title="Les vins sont à présenter fermentation terminée, stabilisés et clarifiés (filtration non obligatoire)"><span class="glyphicon glyphicon-question-sign"></span></a>
					</p>
					<div class="form-group" id="compositions">
						<?php foreach ($form['composition'] as $k => $formComposition): ?>
                        <?php include_partial('tirage/form_composition_item', array('form' => $formComposition)); ?>
                        <?php endforeach; ?>
					</div>
					<a href="#" role="button" class="text-success pull-right btn_ajouter_ligne_template" data-container="#compositions" data-template="#template_compositionsForm"><span class="glyphicon glyphicon-plus-sign" style=""></span>&nbsp;Ajouter une ligne</a>
					<script id="template_compositionsForm" type="text/x-jquery-tmpl">
						<?php echo include_partial('form_composition_item', array('form' => $form->getFormTemplateComposition())); ?>
					</script>
				</div>
			</div>
			
    	</div>    
    	<div class="col-xs-4 col-xs-offset-1"></div>
    </div>

    

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="#" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>au prélèvement</small></a>
        </div>
        <div class="col-xs-6 text-right">
        	<button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small>vers la validation</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
        </div>
    </div>
</form>
