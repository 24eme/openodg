<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<?php if(!isset($demande)): ?>
<div class="row form-group">
    <div class="col-xs-4 text-right control-label">
        Demande :
    </div>
    <div class="col-xs-6">
        <?php if(isset($form['demande'])): ?>
        <span class="text-danger"><?php echo $form['demande']->renderError() ?></span>
        <?php echo $form['demande']->render(array("data-placeholder" => "Séléctionnez une demande", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
        <?php else: ?>
            <p class="form-control-static"><?php echo $demande->getDemandeLibelle() ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php if(isset($form['donnees'])): ?>
    <hr />
<?php if(isset($form['donnees']['produit'])): ?>
<div class="row form-group">
    <div class="col-xs-4 text-right control-label">
        Produit :
    </div>
    <div class="col-xs-6">
        <span class="text-danger"><?php echo $form['donnees']['produit']->renderError() ?></span>
        <?php echo $form['donnees']['produit']->render(array("data-placeholder" => "Séléctionnez un produit", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
    </div>
</div>
<?php endif; ?>
<?php if(isset($form['donnees']['activites'])): ?>
<div class="row form-group">
    <div class="col-xs-4 text-right control-label">
        Activités :
    </div>
    <div class="col-xs-6">
        <span class="text-danger"><?php echo $form['donnees']['activites']->renderError() ?></span>
        <?php $activitesWidget = $form['donnees']['activites']; ?>
            <?php foreach($activitesWidget->getWidget()->getChoices() as $key => $option): ?>
                <div class="checkbox">
                    <label>
                        <input class="acheteur_checkbox" type="checkbox" id="<?php echo $activitesWidget->renderId() ?>_<?php echo $key ?>" name="<?php echo $activitesWidget->renderName() ?>[]" value="<?php echo $key ?>" <?php if(is_array($activitesWidget->getValue()) && in_array($key, $activitesWidget->getValue())): ?>checked="checked"<?php endif; ?> />&nbsp;&nbsp;<?php echo HabilitationClient::$activites_libelles[$key]; ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>
<?php foreach($form['donnees'] as $key => $formDonnee): ?>
    <?php if(in_array($key, array('produit', 'activites'))): continue; endif; ?>
    <div class="row form-group">
        <span class="text-danger"><?php echo $formDonnee->renderError(); ?></span>
        <div class="col-xs-4 control-label text-right">
            <?php echo $formDonnee->renderLabelName(); ?>
        </div>
        <div class="col-xs-6">
            <?php echo $formDonnee->render(array("placeholder" => "", "class" => "form-control", "required" => false)); ?>
        </div>
    </div>
<?php endforeach; ?>
<?php endif; ?>

<hr />
<div class="row form-group">
    <div class="col-xs-4 text-right control-label">
        Statut :
    </div>
    <div class="col-xs-6">
        <span class="text-danger"><?php echo $form['statut']->renderError() ?></span>
        <?php echo $form['statut']->render(array("data-placeholder" => "Séléctionnez un statut", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
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
