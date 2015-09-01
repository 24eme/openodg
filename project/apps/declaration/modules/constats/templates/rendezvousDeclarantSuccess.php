<?php use_helper("Date") ?>


<div class="page-header">
    <h2>Constats Opérateurs</h2>
</div>
<div class="row">    
    <div class="col-xs-12">        
        <div class="list-group">
            <?php foreach ($compte->getChais() as $keyChai => $chai): ?>
                <div class="list-group-item">
                    <form id="form_operateur_rendezvous" action="<?php echo url_for('rendezvous_creation', array('id' => $compte->_id, 'idchai' => $keyChai)); ?>" method="post" class="form-horizontal" name="<?php echo $formsRendezVous[$keyChai]->getName(); ?>">
                        <?php echo $formsRendezVous[$keyChai]->renderHiddenFields(); ?>
                        <?php echo $formsRendezVous[$keyChai]->renderGlobalErrors(); ?>


                        <div class="row">
                            <div class="col-xs-5">
                                <div class="col-xs-12">CHAI <?php echo "" . $keyChai + 1 ?> <small>(modification)</small></div>
                                <div class="col-xs-12"><?php echo $chai->adresse ?></div>
                                <div class="col-xs-6 text-left"><?php echo $chai->commune ?></div>
                                <div class="col-xs-6 text-left"><?php echo $chai->code_postal ?></div>
                            </div>

                            <div class="col-xs-7">
                                <div class="row">
                                    <div class="col-xs-7">
                                        <div class="form-group <?php if ($formsRendezVous[$keyChai]["date"]->hasError()): ?>has-error<?php endif; ?>">
                                            <?php echo $formsRendezVous[$keyChai]['date']->renderError(); ?>
                                            <div class="input-group date-picker" >
                                                <?php echo $formsRendezVous[$keyChai]['date']->render(array('class' => 'form-control')); ?>
                                                <div class="input-group-addon">
                                                    <span class="glyphicon-calendar glyphicon"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-5">
                                        <div class="form-group <?php if ($formsRendezVous[$keyChai]["heure"]->hasError()): ?>has-error<?php endif; ?>">
                                            <?php echo $formsRendezVous[$keyChai]["heure"]->renderError(); ?>

                                            <div class="input-group date-picker-time">
                                                <?php echo $formsRendezVous[$keyChai]["heure"]->render(array("class" => "form-control")); ?>
                                                <div class="input-group-addon">
                                                    <span class="glyphicon glyphicon-time"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <?php echo $formsRendezVous[$keyChai]["commentaire"]->render(array("class" => "form-control")); ?>

                                        </div>
                                    </div>
                                </div>
                                <div class="row row-margin row-button">    
                                    <div class="col-xs-3 text-right">
                                        <button type="submit" class="btn btn-default btn-lg btn-upper">Ajouter</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-3">
        <a href="#" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-center lead text-muted">
        <span data-dynamic-value="nb-lots"><?php echo "3" ?></span> lot(s) pour <span data-dynamic-value="nb-operateurs"><?php echo '3' ?></span> opérateur(s)
    </div>
    <div class="col-xs-3 text-right">
        <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer</button>
    </div>
</div>

