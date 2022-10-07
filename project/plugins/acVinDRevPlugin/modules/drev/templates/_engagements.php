<h2 class="h3">J'ai pris connaissance des pièces à fournir</h2>

<div class="alert" role="alert" id="engagements">
    <div class="form-group">

        <div class="alert alert-danger <?php if(!$form->hasErrors()): ?>hidden<?php endif; ?>" role="alert">
    	    <ul class="error_list">
                <?php
                    foreach($validation->getEngagements() as $engagement):
                        if(($engagement->getCode() == "VIP2C_OUEX_CONDITIONNEMENT") or preg_match('/VIP2C_OUEX_CONTRAT_VENTE_EN_VRAC/',$engagement->getCode())):
                            $VIP2C = true;
                        else:
                            $VIP2C = false;
                            continue;
                        endif;
                    endforeach;
                    if($VIP2C):?> <li class="text-left" style="list-style-type: none;">Merci de ne sélectionner qu' <strong>1</strong> seul engagement qui justifie le dépassement du volume de Méditerrannée rosé</li>
                    <?php else: ?><li class="text-left" style="list-style-type: none;">Merci de sélectionner vos engagements.</li>
                    <?php endif; ?>
    		</ul>
    	</div>

        <?php foreach ($validation->getEngagements() as $engagement):
            if(!$sf_user->isAdmin() && $engagement->getCode()== DRevDocuments::DOC_VIP2C_PAS_INFORMATION){
                continue;
            }
            if (isset($form['engagement_' . $engagement->getCode()])): ?>
        <div class="checkbox-container <?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?>has-error<?php endif; ?>">
            <div class="checkbox">
                <label>
                	<?php echo $form['engagement_' . $engagement->getCode()]->render(); ?>

                    <?php echo $engagement->getRawValue()->getMessage() ?>
                    <?php echo ($engagement->getRawValue()->getInfo()) ? " : <strong>".$engagement->getRawValue()->getInfo() : '</strong>'; ?>
                </label>
            </div>
            </div>
        <?php endif; endforeach; ?>
    </div>
</div>
