<h2 class="h3">Je m'engage à :</h2>

<div class="alert" role="alert" id="engagements">
    <div class="form-group">
        <div class="alert alert-danger <?php if(!$form->hasErrors()): ?>hidden<?php endif; ?>" role="alert">
    	    <ul class="error_list">
                <li class="text-left" style="list-style-type: none;">Merci de sélectionner vos engagements.</li>
    		</ul>
    	</div>

        <?php foreach ($validation->getEngagements() as $engagement):
            if (isset($form['engagement_' . $engagement->getCode()])): ?>
        <div class="checkbox-container <?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?>has-error<?php endif; ?>">
            <div class="checkbox">
                <label>
                	<?php echo $form['engagement_' . $engagement->getCode()]->render(); ?>

                    <?php echo $engagement->getRawValue()->getMessage() ?>
                    <?php echo ($engagement->getRawValue()->getInfo()) ? " : <strong>".$engagement->getRawValue()->getInfo() . '</strong>' : ''; ?>
                </label>
            </div>
            </div>
        <?php else: ?>
        <!-- engagement_<?php echo $engagement->getCode(); ?> not found in  form -->
        <?php endif; endforeach; ?>
    </div>
</div>
