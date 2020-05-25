<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<?php if(!isset($demande)): ?>
<div class="row form-group">
    <div class="col-xs-4 text-right control-label">
        Demande :
    </div>
    <div class="col-xs-6">
        <span class="text-danger"><?php echo $form['demande']->renderError() ?></span>
        <?php echo $form['demande']->render(array("data-placeholder" => "Séléctionnez une demande", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
    </div>
</div>
    <hr />
<?php if(isset($form['produit']) && isset($form['activites'])): ?>
<div class="row form-group">
    <div class="col-xs-4 text-right control-label">
        Produit :
    </div>
    <div class="col-xs-6">
        <span class="text-danger"><?php echo $form['produit']->renderError() ?></span>
        <?php echo $form['produit']->render(array("data-placeholder" => "Séléctionnez un produit", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
    </div>
</div>
<div class="row form-group">
    <div class="col-xs-4 text-right control-label">
        Activités :
    </div>
    <div class="col-xs-6">
        <span class="text-danger"><?php echo $form['activites']->renderError() ?></span>
        <?php $activitesWidget = $form['activites']; ?>
            <?php foreach($activitesWidget->getWidget()->getChoices() as $key => $option): ?>
                <div class="checkbox">
                    <label>
                        <input class="acheteur_checkbox" type="checkbox" id="<?php echo $activitesWidget->renderId() ?>_<?php echo $key ?>" name="<?php echo $activitesWidget->renderName() ?>[]" value="<?php echo $key ?>" <?php if(is_array($activitesWidget->getValue()) && in_array($key, $activitesWidget->getValue())): ?>checked="checked"<?php endif; ?> />&nbsp;&nbsp;<?php echo HabilitationClient::getInstance()->getLibelleActivite($key); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<hr />
<?php endif; ?>
<?php if(!isset($form['produit']) && !isset($form['activites'])): ?>
    <?php foreach($form->getDocument()->getProduitsHabilites() as $produit): ?>
    <div class="row form-group">
        <div class="col-xs-4 text-right control-label">
            Produits :
        </div>
        <div class="col-xs-6">
            <p class="form-control-static"><?php echo $produit->libelle; ?></p>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-xs-4 text-right control-label">
            Activités :
        </div>
        <div class="col-xs-6">
            <ul class="form-control-static list-unstyled">
            <?php foreach($produit->getActivitesHabilites() as $activite): ?>
                <li><?php echo $activite->getLibelle(); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <hr />
    <?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>


<?php if(isset($demande) && isset($form['activites'])): ?>
    <div class="row form-group">
        <div class="col-xs-4 text-right control-label">
            Produit :
        </div>
        <div class="col-xs-6">
            <p class="form-control-static">
                <?php echo $demande->getProduitLibelle(); ?>
            </p>
        </div>
    </div>
    <div id="bloc_activite_info" class="row form-group">
        <div class="col-xs-4 text-right control-label">
            Activites :
        </div>
        <div class="col-xs-6">
            <p class="form-control-static">
                <?php echo implode(", ", $demande->getActivitesLibelle()->getRawValue()); ?> <?php if(count($demande->getActivitesLibelle()) > 1): ?>
                <a onclick="if(!confirm('Étes vous sûr de vouloir sépaper les activités de cette demande ?')) { return false; } document.getElementById('bloc_activite_division').classList.remove('hidden'); document.getElementById('bloc_activite_info').classList.add('hidden'); return false;" href="" style="opacity: 0.6;" class="small">(Séparer)</a><?php endif; ?>
            </p>
        </div>
    </div>
    <div id="bloc_activite_division" class="row form-group hidden">
        <div class="col-xs-4 text-right control-label">
            Activités :
            <br />
            <a onclick="document.getElementById('bloc_activite_info').classList.remove('hidden'); document.getElementById('bloc_activite_division').classList.add('hidden'); return false;" href="" class="small">(Annuler)</a>
        </div>
        <div class="col-xs-6">
            <span class="text-danger"><?php echo $form['activites']->renderError() ?></span>
            <?php $activitesWidget = $form['activites']; ?>
                <?php foreach($activitesWidget->getWidget()->getChoices() as $key => $option): ?>
                    <div class="checkbox">
                        <label>
                            <input class="acheteur_checkbox" type="checkbox" id="<?php echo $activitesWidget->renderId() ?>_<?php echo $key ?>" name="<?php echo $activitesWidget->renderName() ?>[]" value="<?php echo $key ?>" <?php if(is_array($activitesWidget->getValue()) && in_array($key, $activitesWidget->getValue())): ?>checked="checked"<?php endif; ?> />&nbsp;&nbsp;<?php echo HabilitationClient::getInstance()->getLibelleActivite($key); ?>
                        </label>
                    </div>
                <?php endforeach; ?>

        </div>
    </div>
<?php endif; ?>

<div class="row form-group">
    <div class="col-xs-4 text-right control-label">
        Statut :
    </div>
    <div class="col-xs-6">
        <span class="text-danger"><?php echo $form['statut']->renderError() ?></span>
        <?php echo $form['statut']->render(array("data-placeholder" => "Séléctionnez un statut", "class" => "form-control select2 select2-offscreen select2autocomplete select2-statut", "required" => true)) ?>
    </div>
</div>
<div class="row form-group">
    <div class="col-xs-4 text-right control-label">
        Date :
    </div>
    <div class="col-xs-6">
        <span class="text-danger"><?php echo $form['date']->renderError() ?></span>
        <div class="input-group date-picker">
            <?php echo $form['date']->render(array('placeholder' => "Date", "required" => false ,"class" => "form-control")) ?>
            <div class="input-group-addon">
                    <span class="glyphicon-calendar glyphicon"></span>
            </div>
        </div>
    </div>
</div>
<div class="row form-group">
    <span class="text-danger"><?php echo $form['commentaire']->renderError(); ?></span>
    <div class="col-xs-4 control-label text-right">
        Commentaire :
    </div>
    <div class="col-xs-6">
        <?php echo $form['commentaire']->render(array("placeholder" => "Commentaire optionnel", "class" => "form-control", "required" => false)); ?>
    </div>
</div>
