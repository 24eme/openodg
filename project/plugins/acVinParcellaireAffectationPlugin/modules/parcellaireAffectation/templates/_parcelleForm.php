<h3>Identification du produit</h3>
<br/>
<?php if (isset($form['produit'])): ?>
    <span class="error"><?php echo $form['produit']->renderError() ?></span>
    <div class="form-group row">
        <div class="col-xs-4">
            <?php echo $form['produit']->renderLabel(); ?>
        </div>
        <div class="col-xs-8">
            <?php echo $form['produit']->render(array("placeholder" => 'Séléctionner un produit', "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($form['cepage'])): ?>
    <span class="error"><?php echo $form['cepage']->renderError() ?></span>
    <div class="form-group row">
        <div class="col-xs-4">
            <?php echo $form['cepage']->renderLabel(); ?>
        </div>
        <div class="col-xs-8">
            <?php echo $form['cepage']->render(array("placeholder" => "Séléctionner un cépage", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($form['campagne_plantation'])): ?>
    <span class="error"><?php echo $form['campagne_plantation']->renderError() ?></span>
    <div class="form-group row">
        <div class="col-xs-4">
            <?php echo $form['campagne_plantation']->renderLabel(); ?>
        </div>
        <div class="col-xs-6">
            <?php echo $form['campagne_plantation']->render(array("placeholder" => "Saisissez l'année de plantation", "class" => "form-control", "required" => true)) ?>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($form['lieudit'])): ?>
    <span class="error"><?php echo $form['lieudit']->renderError() ?></span>
    <div class="form-group row">
        <div class="col-xs-4">
            <?php echo $form['lieudit']->renderLabel(); ?>
        </div>
        <div class="col-xs-8">
            <?php
            echo $form['lieudit']->render(array("placeholder" => "Saisissez un lieu dit", "class" => "form-control select2 select2-offscreen select2permissifNoAjax",
                "data-choices" => json_encode($form->getLieuDetailForAutocomplete()),
                "required" => false))
            ?>
        </div>
    </div>
<?php endif; ?>

<h3>Identification de la parcelle</h3>
<br/>
<span class="error"><?php echo $form['commune']->renderError() ?></span>
<div class="form-group row">
    <div class="col-xs-4">
        <?php echo $form['commune']->renderLabel(); ?>
    </div>
    <div class="col-xs-8">
        <?php echo $form['commune']->render(array("placeholder" => "Saisissez une commune", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
    </div>
</div>
<span class="error"><?php echo $form['section']->renderError() ?></span>
<div class="form-group row">
    <div class="col-xs-4">
        <?php echo $form['section']->renderLabel(); ?>
    </div>
    <div class="col-xs-6">
        <?php echo $form['section']->render(array("placeholder" => "Saisissez une section", "class" => "form-control", "required" => true, 'pattern' => "[0-9A-Z]+", "title" => "Votre section doit etre composé de lettres en majuscules et de chiffres")) ?>
    </div>
</div>
<span class="error"><?php echo $form['numero_parcelle']->renderError() ?></span>
<div class="form-group row">
    <div class="col-xs-4">
        <?php echo $form['numero_parcelle']->renderLabel(); ?>
    </div>
    <div class="col-xs-6">
        <?php echo $form['numero_parcelle']->render(array("placeholder" => "Saisissez un numéro de parcelle", "class" => "form-control", "required" => true, 'pattern' => "[0-9]+", "title" => "Votre numéro de parcelle doit etre un nombre")) ?>
    </div>
</div>
<span class="error"><?php echo $form['superficie']->renderError() ?></span>
<div class="form-group row">
    <div class="col-xs-4">
        <?php echo $form['superficie']->renderLabel(); ?>
    </div>
    <div class="col-xs-6">
        <div class="input-group">
            <?php echo $form['superficie']->render(array("placeholder" => "Saisissez une supérficie", "class" => "form-control num_float", "required" => true, "title" => "Votre numéro de parcelle doit etre un nombre")) ?>
            <div class="input-group-addon">ares</div>
        </div>
    </div>
</div>
