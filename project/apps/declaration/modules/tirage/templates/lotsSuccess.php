<?php
$global_error_msg = "";
/*foreach ($form->getGlobalErrors() as $item):
    $global_error_msg = $item->getMessage();
    break;
endforeach;*/

$hasError = ($global_error_msg != "");
?>

<div class="page-header no-border">
    <h2>Dégustation conseil <small>Réalisée par l'ODG - AVA</small></h2>
</div>


<form method="post" action="<?php echo url_for('tirage_lots') ?>" role="form" class="form-horizontal">

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
					<div class="form-group ">
						<label class="col-xs-5 control-label">du</label>                            
						<div class="col-xs-7">
							<div class="input-group date-picker">
								<input type="text" class="form-control" value="16/05/2016" name="du" data-date-defaultdate="15/11/2015">
								<div class="input-group-addon">
									<span class="glyphicon-calendar glyphicon"></span>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label class="col-xs-5 control-label">au</label>                            
						<div class="col-xs-7">
							<div class="input-group date-picker">
								<input type="text" class="form-control" value="16/05/2016" name="au" data-date-defaultdate="15/11/2015">
								<div class="input-group-addon">
									<span class="glyphicon-calendar glyphicon"></span>
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
						<div class="row compositionBouteilles">
							<div class="col-xs-2 col-xs-offset-3">
								<input class="form-control input-rounded num_int text-right" type="text" value="" name="nb_b">
							</div>
							<div class="col-xs-3 text-left" style="padding-top: 7px;">bouteille(s) de</div>
							<div class="col-xs-3">
									<select class="form-control">
									  <option>1 cl</option>
									  <option>2 cl</option>
									  <option>3 cl</option>
									  <option>4 cl</option>
									  <option>5 cl</option>
									</select>
							</div>
							<div class="col-xs-1" style="padding-top: 5px;">
								<a href="javascript:$.initCollectionDeleteTemplate();" data-container="div.compositionBouteilles" role="button" class="text-danger btn_rm_ligne_template" style="font-size: 20px;"><span class="glyphicon glyphicon-remove-sign"></span></a>
							</div>
						</div>
					</div>
					<a href="#" role="button" class="text-success pull-right btn_ajouter_ligne_template" data-container="#compositions" data-template="#template_compositionsForm"><span class="glyphicon glyphicon-plus-sign" style=""></span>&nbsp;Ajouter une ligne</a>
					<script id="template_compositionsForm" type="text/x-jquery-tmpl">
						<div class="row compositionBouteilles">
							<div class="col-xs-2 col-xs-offset-3">
								<input class="form-control input-rounded num_int text-right" type="text" value="" name="nb_b">
							</div>
							<div class="col-xs-3 text-left" style="padding-top: 7px;">bouteille(s) de</div>
							<div class="col-xs-3">
									<select class="form-control">
									  <option>1 cl</option>
									  <option>2 cl</option>
									  <option>3 cl</option>
									  <option>4 cl</option>
									  <option>5 cl</option>
									</select>
							</div>
							<div class="col-xs-1" style="padding-top: 5px;">
								<a href="javascript:$.initCollectionDeleteTemplate();" data-container="div.compositionBouteilles" role="button" class="text-danger btn_rm_ligne_template" style="font-size: 20px;"><span class="glyphicon glyphicon-remove-sign"></span></a>
							</div>
						</div>
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
