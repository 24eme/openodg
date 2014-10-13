<h2 class="h3">Engagements</h2>

<div class="alert" role="alert">
    <strong>Je m'engage à :</strong>
    <div class="form-group">
    <?php if($form->hasErrors()): ?>
    <ul class="error_list">
		<li class="text-left">Vous devez vous engager pour valider.</li>
	</ul>
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
