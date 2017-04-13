<tr class="dynamic-element-item" id="saisie_item_<?php echo $form->getName() ?>">
    <td class="form-group">
        <?php echo $form["compte"]->render(array("class" => "form-control select2 select2-offscreen select2autocompleteremote", "placeholder" => "Chercher un dégustateur", "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_DEGUSTATEUR)))); ?>
    </td>
    <td class="form-group text-center">
        <button tabindex="-1" type="button" data-add=".dynamic-element-add" data-lines="#saisie_container .dynamic-element-item" data-line="#saisie_item_<?php echo $form->getName() ?>" class="btn btn-default-step dynamic-element-delete"><span class="glyphicon glyphicon-remove"></span></button>
    </td>
</tr>
