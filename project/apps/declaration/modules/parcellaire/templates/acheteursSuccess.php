<?php include_partial('step', array('parcellaire' => $parcellaire, 'step' => 'acheteurs', 'identifiant' => 'XXX')); ?>

<div class="page-header">
    <h2>Saisie des acheteurs</h2>
</div>


<form action="" method="post" class="">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

    <div class="row">       
        <div class="col-xs-12">
            <div id="listes_cepages" class="list-group">
                <table class="table table-striped">
                    <tr>
                        <th></th>
                        <?php foreach($form->getAcheteurs() as $libelle): ?>           
                        <th><?php echo $libelle ?></th>
                        <?php endforeach; ?>
                        <th>
                    <div class="text-center">
                        <button class="btn btn-warning ajax btn-sm" data-toggle="modal" data-target="#popupForm" type="button"><span class="eleganticon icon_plus"></span></button>
                    </div>
                    </th>        
                    </tr>
                    <?php foreach($form as $key => $field) : ?>
                    <?php if($field->isHidden()) { continue; } ?>
                    <tr>
                        <td>
                            <?php echo $field->renderLabel() ?>
                            <?php echo $field->renderError() ?>
                        </td>
                        <?php foreach($field->getWidget()->getChoices() as $key => $option): ?>            
                        <td><input type="checkbox" id="<?php echo $field->renderId() ?>_<?php echo $key ?>" name="<?php echo $field->renderName() ?>[]" value="<?php echo $key ?>" <?php if(in_array($key, $field->getValue())): ?>checked="checked"<?php endif; ?> /></td>
                        <?php endforeach; ?>
                        <td></td> 
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-default-step btn-lg btn-upper">Continuer</a>
        </div>
    </div>
</form>
