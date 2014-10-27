<?php
$global_error_msg = "";
foreach ($form->getGlobalErrors() as $item):
    $global_error_msg = $item->getMessage();
    break;
endforeach;

$hasError = ($global_error_msg != "");
?>

<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<div class="page-header no-border">
    <h2>Dégustation conseil <small>Réalisée par l'ODG - AVA</small></h2>
</div>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'lot_grdcru', 'drev' => $drev)) ?>

<form method="post" action="<?php echo url_for('drev_lots', $drev->addPrelevement(Drev::CUVE_GRDCRU)); ?>" role="form" class="ajaxForm">
    <?php if ($hasError): ?>
        <div class="alert alert-danger" role="alert"><?php echo $global_error_msg; ?></div>
    <?php endif; ?>
    <p>Veuillez indiquer le nombre de lots susceptibles d'être prélevés en AOC Alsace Grand Cru.</p>
    <?php include_partial('drev/lotsForm', array('drev' => $drev, 'form' => $form, 'ajoutForm' => $ajoutForm, 'title' => 'Lieux dits – Cépages')); ?>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("drev_lots", $prelevement) ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>aux lots précédents</small></a>
        </div>
        <div class="col-xs-6 text-right">
        	<?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
	        <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
	        <?php else: ?>
	        <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small>vers le contrôle externe</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
	        <?php endif; ?>
        </div>
    </div>
</form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for("drev_lots_ajout", $prelevement), 'form' => $ajoutForm)); ?>

