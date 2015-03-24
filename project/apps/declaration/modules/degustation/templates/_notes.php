<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<?php $notes = $degustation->getNotes(); ?>
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
                <?php foreach ($degustation->getNotes() as $note): ?>
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
                                <?php echo $form[$note->prelevement->getHashForKey()]->renderError(); ?>
                                <?php echo $form[$note->prelevement->getHashForKey()]->render(array('class' => 'form-control select2')); ?>
                        <?php else: ?>
                                <?php echo getTypeCourrier($note->prelevement); ?>
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