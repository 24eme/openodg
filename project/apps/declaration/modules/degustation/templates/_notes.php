<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<?php $notes = $degustation->getNotes(); ?>

<?php if (isset($form) && $form): ?>
    <form action="" method="post" class="form-horizontal">
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
                    <?php if (isset($form) && $form): ?>
                        <th>Type courrier</th> 
                    <?php endif; ?>
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
                        <?php if (isset($form) && $form): ?>
                            <td>
                                <?php echo $form[$note->prelevement->getHashForKey()]->renderError(); ?>
                                <?php echo $form[$note->prelevement->getHashForKey()]->render(array('class' => 'form-control select2')); ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <?php if (isset($form) && $form): ?>
    </form>
<?php endif; ?>