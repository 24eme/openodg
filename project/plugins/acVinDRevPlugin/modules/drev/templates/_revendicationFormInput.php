<?php use_helper('Float') ?>
<td style="position: relative;" class="<?php echo (isset($tdClass)) ? $tdClass : "" ?>">
    <?php if(isset($prefixe)): ?>
    <small style="position:absolute; top: 14px; left: 14px;" class="text-muted"><?php echo $prefixe ?></small>
    <?php endif; ?>
    <?php if (isset($form[$name.(($vtsgn) ? "_vtsgn" : null)])): ?>
    <?php
    $global_error_class = ((($global_error_class == 'error_field_to_focused')) ||
    ('drev_produits[produits]' . $global_error_id == $form[$name.(($vtsgn) ? "_vtsgn" : null)]->renderName())) ?
    'error_field_to_focused' : '';
    ?>
    <div class="<?php if ($global_error_class): ?>has-error<?php endif; ?>">
        <?php echo $form[$name.(($vtsgn) ? "_vtsgn" : null)]->renderError() ?>
        <?php echo $form[$name.(($vtsgn) ? "_vtsgn" : null)]->render(array('placeholder' => $placeholder)) ?>
    </div>
    <?php /*else: ?>
        <?php echoFloat($produit->get("detail".(($vtsgn) ? "_vtsgn" : null))->get($name)); ?>
    <?php */ endif; ?>
</td>
