<h2 class="h3">Je m'engage à fournir <?php if (count($validation->getPoints(DrevValidation::TYPE_ENGAGEMENT)) > 1): ?>les pièces suivantes<?php else: ?>la pièce suivante<?php endif; ?></h2>

<div class="alert" role="alert" id="engagements">
    <div class="form-group">
    <?php if($form->hasErrors()): ?>
    <div class="alert alert-danger" role="alert">
	    <ul class="error_list">
			<li class="text-left">Vous devez vous engager pour valider.</li>
		</ul>
	</div>
    <?php endif; ?>
    <?php foreach ($validation->getPoints(DrevValidation::TYPE_ENGAGEMENT) as $engagement): ?>
    <?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?><div class="has-error"><?php endif; ?>
    <div class="checkbox<?php if($engagement->getCode() == DRevDocuments::DOC_DR && $drev->hasDr()): ?> disabled<?php endif; ?>">
        <label>
        	<?php 
        		if ($engagement->getCode() == DRevDocuments::DOC_DR && $drev->hasDr()) {
        			echo $form['engagement_' . $engagement->getCode()]->render(array('checked' => 'checked'));
        		} else {
        			echo $form['engagement_' . $engagement->getCode()]->render();
        		}
        	?>
            <?php echo $engagement->getRawValue()->getMessage() ?>
            <?php if ($engagement->getCode() == DRevDocuments::DOC_DR && $drev->hasDr()): ?>- <a href="<?php echo $drev->getAttachmentUri('DR.pdf'); ?>" target="_blank"><small>Télécharger ma DR</small></a><?php endif; ?>
        </label>
    </div>
    <?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?></div><?php endif; ?>
    <?php endforeach; ?>
    </div>
</div>
