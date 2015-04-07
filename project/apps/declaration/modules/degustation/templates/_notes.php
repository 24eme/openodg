<?php use_javascript("degustation.js", "last") ?>
<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>

<?php $notes = $tournee->getNotes(); ?>
<?php $hasForm = isset($form) && $form; ?>
    <?php if ($hasForm): ?>
    <form action="<?php echo url_for('degustation_courriers', $degustation); ?>" method="post" class="form-horizontal">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
    <?php endif; ?>
    <div class="row">    
        <div class="col-xs-12">        
            <table class="table table-striped">
                <tr>
                    <th>N° Anon.</th>            
                    <th>Opérateur</th> 
                    <th>Libellé produit</th> 
                    <th>Notes</th> 
                    <th>Appreciation</th> 
                    <th>Type courrier</th> 
                </tr>
                <?php foreach ($tournee->getNotes() as $note): ?>
                    <tr>
                        <td><?php echo $note->prelevement->anonymat_degustation; ?></td>
                        <td><?php echo $note->operateur->raison_sociale; ?></td> 
                        <td><?php echo $note->prelevement->libelle; ?></td> 
                        <td>
                            <ul style="margin: 0; padding: 0" >
                                <?php
                                foreach ($note->prelevement->notes as $noteType => $noteQualifie):
                                    $defautsStr = "";
                                    foreach ($noteQualifie->defauts as $key => $defaut):
                                        $defautsStr .= $defaut;
                                        if ($key != count($noteQualifie->defauts) - 1):
                                            $defautsStr .= ', ';
                                        endif;
                                    endforeach;
                                    ?>
                                    <li>
                                        <?php
                                        $class = (count($noteQualifie->defauts)) ? "text-danger" : "text-success";
                                        echo DegustationClient::$note_type_libelles[$noteType] . ' : <strong class="pull-right ' . $class . ' ">' . $noteQualifie->note . '</strong>';
                                        if (count($noteQualifie->defauts)):
                                            echo "<br/><small class='text-muted'>" . $defautsStr . "</small>";
                                        endif;
                                        ?> 
                                    </li>                                
                                <?php endforeach; ?>
                            </ul>
                        </td> 
                        <td><?php echo $note->prelevement->appreciations; ?></td> 
                        <td class="text-center">
                            <?php if ($hasForm): ?>
                                <div class="type_courrier_for_visite" id="<?php echo $note->prelevement->getHashForKey(); ?>">
                                   
                                        <?php echo $form[$note->prelevement->getHashForKey()]->renderError(); ?>
                                        <?php echo $form[$note->prelevement->getHashForKey()]->render(array('class' => 'form-control select2')); ?>
                                    <div id="<?php echo 'visite_date_degustation_courrier_' . $note->prelevement->getHashForKey(); ?>" style="<?php echo ($note->prelevement->exist('type_courrier') && $note->prelevement->type_courrier == DegustationClient::COURRIER_TYPE_VISITE)? '' : 'display:none;' ?>" >
                                    <div style="padding-top: 10px;" class="input-group date-picker" >
                                            <?php echo $form['visite_date_' . $note->prelevement->getHashForKey()]->renderError(); ?>
                                            <?php echo $form['visite_date_' . $note->prelevement->getHashForKey()]->render(array('class' => 'form-control')); ?>
                                            <div class="input-group-addon">
                                                <span class="glyphicon-calendar glyphicon"></span>
                                            </div>
                                        </div>
                                        <div style="padding-top: 10px;" class="input-group date-picker-time" >
                                            <?php echo $form['visite_heure_' . $note->prelevement->getHashForKey()]->renderError(); ?>
                                            <?php echo $form['visite_heure_' . $note->prelevement->getHashForKey()]->render(array('class' => 'form-control')); ?>
                                            <div class="input-group-addon">
                                                <span class="glyphicon glyphicon-time"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            <?php else: ?>        
                                <?php if ($note->prelevement->exist('type_courrier') && $note->prelevement->type_courrier): ?>
                                    <a href="<?php echo url_for('degustation_courrier_prelevement', array('id' => $degustation->_id, 'hash_prelevement' => $note->prelevement->getHashForKey())) ?>">
                                    <?php endif; ?>
                                    <?php echo getTypeCourrier($note->prelevement); ?>
                                    <?php if ($note->prelevement->exist('type_courrier') && $note->prelevement->type_courrier): ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6 text-left">
            <?php if ($hasForm): ?>
                <a class="btn btn-primary btn-lg btn-upper" href="<?php echo url_for('degustation') ?>"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
            <?php else : ?>
                <a class="btn btn-primary btn-lg btn-upper" href="<?php echo url_for('degustation_visualisation', $degustation) ?>"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
            <?php endif; ?>
        </div>             
        <div class="col-xs-6 text-right">
            <?php if ($degustation->validation && $degustation->date > date('Y-m-d')): ?>
                <a class="btn btn-warning btn-lg" href="<?php echo url_for('degustation_organisation', $degustation) ?>"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Modifier l'organisation des tournées</a>
            <?php endif; ?>
            <?php if ($hasForm): ?>
                <button type="submit" class="pull-right btn btn-default btn-md btn-upper" >Enregistrer type courrier<span class="eleganticon arrow_carrot-right"></span></button>
                <?php endif; ?>
        </div>
    </div>
    <?php if ($hasForm): ?>
    </form>
<?php endif; ?>