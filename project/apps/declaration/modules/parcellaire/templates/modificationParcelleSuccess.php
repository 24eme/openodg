<form method="post" action="" role="form" class="form-horizontal" novalidate="novalidate">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title" id="myModalLabel">Modifier la parcelle</h2>
    </div>
    <div class="modal-body">                    
        <h3>Identification du produit</h3>
        <br/>
        <?php if (isset($form['lieuCepage'])): ?>
            <span class="error"><?php echo $form['lieuCepage']->renderError() ?></span>
            <div class="form-group row">
                <div class="col-xs-3">
                    <?php echo $form['lieuCepage']->renderLabel(); ?>
                </div>
                <div class="col-xs-1">
                    <a class="btn-tooltip btn btn-lg" data-toggle="tooltip" data-placement="auto" title="" data-original-title="Le choix du lieu-dit/cépage peut se faire en recherchant directement dans le champ" >
                        <span class="glyphicon glyphicon-question-sign"></span>
                    </a>
                </div>
                <div class="col-xs-8">
                    <?php echo $form['lieuCepage']->render(array("placeholder" => "Saisissez un lieu-dit/cépage", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
                </div>
            </div>
        <?php else: ?>
            <span class="error"><?php echo $form['lieuDit']->renderError() ?></span>
            <div class="form-group row">
                <div class="col-xs-4">
                    <?php echo $form['lieuDit']->renderLabel(); ?>
                </div>
                <div class="col-xs-8">
                    <?php
                    echo $form['lieuDit']->render(array("placeholder" => "Saisissez un lieu dit", "class" => "form-control select2 select2-offscreen select2permissifNoAjax",
                        "data-choices" => json_encode($form->getLieuDetailForAutocomplete()),
                        "required" => true))
                    ?>
                </div>
            </div>
            <span class="error"><?php echo $form['cepage']->renderError() ?></span>
            <div class="form-group row">
                <div class="col-xs-4">
                    <?php echo $form['cepage']->renderLabel(); ?>
                </div>
                <div class="col-xs-8">
                    <?php echo $form['cepage']->render(array("placeholder" => "Saisissez cépage", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
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
            <div class="col-xs-4">
                <?php echo $form['section']->render(array("placeholder" => "Saisissez une section", "class" => "form-control", "required" => true, 'pattern' => "[0-9A-Z]+", "title" => "Votre section doit etre composé de lettres en majuscules et de chiffres")) ?>
            </div>
        </div>
        <span class="error"><?php echo $form['numero_parcelle']->renderError() ?></span>
        <div class="form-group row">
            <div class="col-xs-4">
                <?php echo $form['numero_parcelle']->renderLabel(); ?>
            </div>
            <div class="col-xs-4">
                <?php echo $form['numero_parcelle']->render(array("placeholder" => "Saisissez un numéro de parcelle", "class" => "form-control", "required" => true, 'pattern' => "[0-9]+", "title" => "Votre numéro de parcelle doit etre un nombre")) ?>
            </div>
        </div>
        <span class="error"><?php echo $form['superficie']->renderError() ?></span>
        <div class="form-group row">
            <div class="col-xs-4">
                <?php echo $form['superficie']->renderLabel(); ?>
            </div>
            <div class="col-xs-4">
                <div class="input-group">
                    <?php echo $form['superficie']->render(array("placeholder" => "Saisissez une supérficie", "class" => "form-control num_float", "required" => true, "title" => "Votre numéro de parcelle doit etre un nombre")) ?>
                    <div class="input-group-addon">hl</div>
                </div>
            </div>
        </div>                                    
    </div>
    <div class="modal-footer">
        <a href="<?php echo url_for("parcellaire_parcelles", array('id' => $parcellaire->_id, 'appellation' => $appellation)) ?>" class="btn btn-danger btn pull-left">Annuler</a>
        <button type="submit" class="btn btn-default btn pull-right">Valider la modification</button>
    </div>
</form>