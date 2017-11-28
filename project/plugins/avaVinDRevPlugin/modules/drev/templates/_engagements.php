<h2 class="h3">J'ai pris connaissance des pièces à fournir</h2>

<div class="alert" role="alert" id="engagements">
    <div class="form-group">
   
        <div class="alert alert-danger <?php if(!$form->hasErrors()): ?>hidden<?php endif; ?>" role="alert">
    	    <ul class="error_list">
    			<li class="text-left">Vous devez cocher pour valider votre déclaration.</li>
    		</ul>
    	</div>
        
        <?php foreach ($validation->getPoints(DrevValidation::TYPE_ENGAGEMENT) as $engagement): ?>
        <div class="checkbox-container <?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?>has-error<?php endif; ?>">
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
            </div>
        <?php endforeach; ?>
    </div>
</div>
