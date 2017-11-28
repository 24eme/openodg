<?php use_helper('DRevMarc'); ?>
<?php include_partial('drevmarc/breadcrumb', array('drevmarc' => $drevmarc )); ?>
<?php include_partial('drevmarc/step', array('step' => 'revendication', 'drevmarc' => $drevmarc)); ?>
<?php $hasError = false; ?>

<div class="page-header">
    <h2>Revendication</h2>
</div>

<form role="form" action="<?php echo url_for("drevmarc_revendication", $drevmarc) ?>" method="post" class="ajaxForm" id="drevmarc_form">

    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

    <p></p>
    <div class="row">
        <div class="col-xs-11">
            <table class="table table-striped ">
                <tbody>
                    <tr>
                        <td  class="col-xs-5">
                            <label class="control-label" for="">Période de distillation :</label>
                        </td>
                        <td class="col-xs-7 form-inline">
                            <div class="form-group col-xs-6">
                                <?php $errorClass = getErrorClass($form['debut_distillation']->renderError(), $hasError); ?>
                                <div class="input-group date-picker-all-days <?php if ($errorClass): ?>has-error<?php endif; ?>">
                                    <?php echo $form['debut_distillation']->render(array('class' => 'text-right  form-control ' . $errorClass, 'placeholder' => 'Du')); ?>
                                    <div class="input-group-addon">
                                        <span class="glyphicon-calendar glyphicon"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-xs-6">
                                <?php $errorClass = getErrorClass($form['fin_distillation']->renderError(), $hasError); ?>
                                <div class="input-group date-picker-all-days <?php if ($errorClass): ?>has-error<?php endif; ?>">
                                    <?php echo $form['fin_distillation']->render(array('class' => 'text-right  form-control ' . $errorClass, 'placeholder' => 'Au')); ?>
                                    <div class="input-group-addon">
                                        <span class="glyphicon-calendar glyphicon"></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    $periode_distillation_error = false;
                    foreach ($form->getFormFieldSchema() as $key => $item):
                        if ($item instanceof sfFormField && $item->hasError()):
                            $periode_distillation_error = ($item->renderId() == "drevmarc_revendication_debut_distillation") || ($item->renderId() == "drevmarc_revendication_fin_distillation");
                            if ($periode_distillation_error):
                                break;
                            endif;
                        endif;
                    endforeach;
                    if ($periode_distillation_error):
                        ?>
                        <tr>
                            <td class="col-xs-5"></td>
                            <td class="col-xs-7 form-inline">
                                <div class="form-group">
                                    <div class="col-xs-6">
                                        <span class="text-danger"><?php echo $form['debut_distillation']->renderError(); ?></span>
                                    </div>
                                    <div class="col-xs-6">
                                        <span class="text-danger"><?php echo $form['fin_distillation']->renderError(); ?></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                        <?php endif; ?>
                        <td  class="col-xs-5">
                            <?php echo $form['qte_marc']->renderLabel(null, array('class' => 'control-label')); ?>
                        </td>
                        <td class="col-xs-7 form-group">
                            <?php $errorClass = getErrorClass($form['qte_marc']->renderError(), $hasError); ?>
                            <div class="col-xs-6  <?php if ($errorClass): ?>has-error<?php endif; ?>">
                                <?php echo $form['qte_marc']->render(array('class' => 'form-control input-rounded text-right ' . $errorClass)); ?>
                            </div>
                            <div class="col-xs-4">
                                <span>kg</span>
                            </div>
                        </td>
                    </tr>
                    <?php if ($form['qte_marc']->renderError() !== ""): ?>
                        <tr>
                            <td  class="col-xs-5">
                            </td>
                            <td class="col-xs-7 form-group">
                                <span class="text-danger"><?php echo $form['qte_marc']->renderError(); ?></span>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td  class="col-xs-5">
                            <?php echo $form['volume_obtenu']->renderLabel(null, array('class' => 'control-label')); ?>
                        </td>
                        <td class="col-xs-7 form-group">
                            <?php $errorClass = getErrorClass($form['volume_obtenu']->renderError(), $hasError); ?>
                            <div class="col-xs-6  <?php if ($errorClass): ?>has-error<?php endif; ?>">
                                <?php echo $form['volume_obtenu']->render(array('class' => 'form-control input-rounded text-right ' . $errorClass)); ?>
                            </div>
                            <div class="col-xs-4">
                                <span>hl d'alcool pur</span>
                            </div>
                        </td>
                    </tr>
                    <?php if ($form['volume_obtenu']->renderError() !== ""): ?>
                        <tr>
                            <td  class="col-xs-5">
                            </td>
                            <td class="col-xs-7 form-group">
                                <span class="text-danger"><?php echo $form['volume_obtenu']->renderError(); ?></span>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td  class="col-xs-5">
                            <?php echo $form['titre_alcool_vol']->renderLabel(null, array('class' => 'control-label')); ?>
                        </td>
                        <td class="col-xs-7 form-group">
                            <?php $errorClass = getErrorClass($form['titre_alcool_vol']->renderError(), $hasError); ?>
                            <div class="col-xs-6  <?php if ($errorClass): ?>has-error<?php endif; ?>">
                                <?php echo $form['titre_alcool_vol']->render(array('class' => 'form-control input-rounded text-right ' . $errorClass)); ?>
                            </div>
                            <div class="col-xs-4">
                                <span>°</span>
                            </div>
                        </td>
                    </tr>
                    <?php if ($form['titre_alcool_vol']->renderError() !== ""): ?>
                        <tr>
                            <td  class="col-xs-5">
                            </td>
                            <td class="col-xs-7 form-group">
                                <span class="text-danger"><?php echo $form['titre_alcool_vol']->renderError(); ?></span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-xs-2"></div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-6"><a href="<?php echo url_for("drevmarc_exploitation", $drevmarc) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a></div>
        <div class="col-xs-6 text-right"><button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small>vers la validation</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button></div>
    </div>
</form>
