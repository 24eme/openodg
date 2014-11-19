<?php if(count($drev->getOrAdd('documents')->toArray()) > 0 || $drev->hasDr()): ?>
<h3>Documents à joindre</h3>
<?php if ($form): ?>
	<form action="<?php echo url_for('drev_visualisation', $drev) ?>" method="post">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
<?php endif; ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-left col-md-9">Documents</th>
            <th class="text-center col-md-3">Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php if($drev->hasDr()): ?>
            <tr>
                <td class="text-left"><?php echo DRevDocuments::getDocumentLibelle(DRevDocuments::DOC_DR) ?></td>
                <td class="text-center"><a class="text-success" href="<?php echo url_for("drev_dr_pdf", $drev) ?>" target="_blank">Télécharger</a></td>
            </tr>
        <?php endif; ?>
    	<?php if (isset($form)): ?>
    		<?php foreach ($form->getEmbeddedForms() as $key => $documentForm): ?>
	        <tr>
	            <td class="text-left"><?php echo DRevDocuments::getDocumentLibelle($key) ?></td>
	            <td class="text-left">
	            	<div class="checkbox">
				        <label>
				        	<?php echo $form[$key]['statut']->render(); ?>
				        	<?php echo $form[$key]['statut']->renderLabel(); ?>
				        </label>
				    </div>
	            </td>
	        </tr>
    		<?php endforeach; ?>
    	<?php else: ?>
	        <?php foreach($drev->getOrAdd('documents') as $document): ?>
	        <tr>
	            <td class="text-left"><?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?></td>
	            <td class="text-center"><span class="<?php if($document->statut == DRevDocuments::STATUT_RECU): ?>text-success<?php else: ?>text-warning<?php endif; ?>"><?php echo DRevDocuments::getStatutLibelle($document->statut) ?></span></td>
	        </tr>
	        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php if ($form): ?>
<div class="col-xs-3 pull-right text-center">
	<button type="submit" class="btn btn-default btn-sm btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Enregistrer</button>
</div>
</form>
<?php endif; ?>
<?php endif; ?>
