<div class="modal fade" id="<?php if (!isset($html_id)): ?>popupForm<?php else: ?><?php echo $html_id ?><?php endif; ?>" role="dialog" aria-labelledby="Ajouter un produit" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo $url ?>" role="form" class="form-horizontal">
                <?php echo $form->renderHiddenFields(); ?>
                <?php echo $form->renderGlobalErrors(); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h2 class="modal-title" id="myModalLabel">Ajouter une parcelle</h2>
                </div>
                <div class="modal-body">
                    <h3>Identification de la parcelle</h3>
                    <br/>
                    <span class="error"><?php echo $form['commune']->renderError() ?></span>
                    <div class="form-group row">
                        <div class="col-xs-4">
                            <?php echo $form['commune']->renderLabel(); ?>
                        </div>
                        <div class="col-xs-8">
                            <?php echo $form['commune']->render(array("placeholder" => "Saisissez une commune", "class" => "form-control", "required" => true)) ?>
                        </div>
                    </div>
                    <span class="error"><?php echo $form['section']->renderError() ?></span>
                    <div class="form-group row">
                        <div class="col-xs-4">
                            <?php echo $form['section']->renderLabel(); ?>
                        </div>
                        <div class="col-xs-8">
                            <?php echo $form['section']->render(array("placeholder" => "Saisissez une section", "class" => "form-control", "required" => true)) ?>
                        </div>
                    </div>
                    <span class="error"><?php echo $form['numero_parcelle']->renderError() ?></span>
                    <div class="form-group row">
                        <div class="col-xs-4">
                            <?php echo $form['numero_parcelle']->renderLabel(); ?>
                        </div>
                        <div class="col-xs-8">
                            <?php echo $form['numero_parcelle']->render(array("placeholder" => "Saisissez un numéro de parcelle", "class" => "form-control", "required" => true)) ?>
                        </div>
                    </div>                    
                    <h3>Identification du produit</h3>
                    <br/>
                    <?php if(isset($form['lieuCepage'])): ?>
                    <span class="error"><?php echo $form['lieuCepage']->renderError() ?></span>
                    <div class="form-group row">
                        <div class="col-xs-4">
                            <?php echo $form['lieuCepage']->renderLabel(); ?>
                        </div>
                        <div class="col-xs-8">
                            <?php echo $form['lieuCepage']->render(array("placeholder" => "Saisissez un lieu/cépage", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <span class="error"><?php echo $form['lieuDit']->renderError() ?></span>
                    <div class="form-group row">
                        <div class="col-xs-4">
                            <?php echo $form['lieuDit']->renderLabel(); ?>
                        </div>
                        <div class="col-xs-8">
                            <?php echo $form['lieuDit']->render(array("placeholder" => "Saisissez un lieu dit", "class" => "form-control select2 select2-offscreen select2permissifNoAjax", 
                                "data-choices" => json_encode($form->getLieuDetailForAutocomplete()),
                                "required" => true)) ?>
                        </div>
                    </div>
                    <span class="error"><?php echo $form['cepage']->renderError() ?></span>
                    <div class="form-group row">
                        <div class="col-xs-4">
                            <?php echo $form['cepage']->renderLabel(); ?>
                        </div>
                        <div class="col-xs-8">
                            <?php echo $form['cepage']->render(array("placeholder" => "Saisissez cépage", "class" => "form-control", "required" => true)) ?>
                        </div>
                    </div>
                    <?php endif; ?>                  
                </div>
                <div class="modal-footer">
                    <a class="btn btn-danger btn pull-left" data-dismiss="modal">Annuler</a>
                    <button type="submit" class="btn btn-default btn pull-right">Ajouter la parcelle</button>
                </div>
            </form>
        </div>
    </div>
</div>
