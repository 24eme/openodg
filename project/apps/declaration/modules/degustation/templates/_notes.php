<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<?php $notes = $tournee->getNotes(); ?>
<h2>Notes obtenues&nbsp;<div class="btn btn-default btn-sm"><?php echo count($notes); ?>&nbsp;vins dégustés</div></h2>

<div class="row">    
    <div class="col-xs-12">        

        <table class="table table-striped">
            <tr>
                <th>N° Anon.</th>            
                <th>Opérateur</th> 
                <th>Libellé produit</th> 
                <th>Notes</th> 
                <th>Appreciation</th> 
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
                                    $class = (count($noteQualifie->defauts))? "text-danger" : "text-success";
                                    echo DegustationClient::$note_type_libelles[$noteType] . ' : <strong class="pull-right '.$class.' ">' . $noteQualifie->note.'</strong>';
                                    if (count($noteQualifie->defauts)):
                                        echo "<br/><small class='text-muted'>".$defautsStr."</small>";
                                    endif;
                                    ?> 
                                </li>
                        <?php endforeach; ?>
                        </ul>
                    </td> 
                    <td><?php echo $note->prelevement->appreciations; ?></td> 
                </tr>
                <?php endforeach; ?>
        </table>
    </div>
</div>