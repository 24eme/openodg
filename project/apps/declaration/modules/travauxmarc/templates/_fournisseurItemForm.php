<tr id="travaux_marc_fournisseurs_item_<?php echo $form->getName() ?>" class="dynamic-element-item">
    <td>
        <?php echo $form["nom"]->render(array("class" => "form-control", "placeholder" => "Nom du fournisseur", "data-norequired" => true)); ?>
        <?php echo $form["nom"]->renderError(); ?>
    </td>
    <td>
        <div class="input-group date-picker">
            <?php echo $form['date_livraison']->render(array('class' => 'form-control', 'placeholder' => 'Date de livraison', "data-norequired" => true)); ?>
            <div class="input-group-addon">
                <span class="glyphicon-calendar glyphicon"></span>
            </div>
        </div>
        <?php echo $form["date_livraison"]->renderError(); ?>
    </td>
    <td>
        <?php echo $form["quantite"]->render(array('class'=> "form-control text-right", 'placeholder' => "Quantité en kg", "data-norequired" => true)); ?>
        <?php echo $form["quantite"]->renderError(); ?>
    </td>
    <td class="text-center">
        <button tabindex="-1" data-confirm="Êtes-vous sur de vouloir supprimer cette ligne ?" type="button" data-add=".dynamic-element-add" data-lines="#travaux_marc_fournisseurs_container .dynamic-element-item" data-line="#travaux_marc_fournisseurs_item_<?php echo $form->getName() ?>" class="btn btn-default-step dynamic-element-delete"><span class="glyphicon glyphicon-remove"></span></button>
    </td>
</tr>
