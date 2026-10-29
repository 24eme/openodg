<?php use_helper('Degustation') ?>
<?php use_helper('Date') ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li class="active"><a href=""><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->getIdentifiant() ?>)</a></li>
</ol>

<div class="row">
    <div class="col-xs-12">
        <h3>Liste des prélévements dégustés</h3>
        <?php if(count($degustations)): ?>
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th class="col-xs-2">Date</th>
                    <th class="col-xs-3">Produit</th>
                    <th class="col-xs-4">Notes et Appreciation</th>
                    <th class="col-xs-2">N°</th>
                    <th class="col-xs-1">Courrier</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($degustations as $degustation): ?>
                <?php foreach($degustation->prelevements as $prelevement): ?>
                    <?php if(!$prelevement->isDegustationTerminee()): continue; endif; ?>
                    <tr>
                        <td><a href="<?php echo url_for("degustation_visualisation", array('id' => str_replace("DEGUSTATION-".$degustation->identifiant."-", "TOURNEE-", $degustation->_id))) ?>"><?php echo format_date($degustation->date_degustation, "P", "fr_FR") ?></a></td>
                        <td><?php echo $prelevement->libelle; ?><?php if($prelevement->exist('fermentation_lactique')): ?><small><br />FML</small><?php endif; ?><?php if($prelevement->composition): ?><small><br /><?php echo $prelevement->composition; ?></small><?php endif; ?><?php if($prelevement->libelle_produit): ?><small class="text-muted"><br /><?php echo $prelevement->libelle_produit ?></small><?php endif; ?></td>
                        <td><ul style="margin: 0; padding: 0" >
                            <?php
                            foreach ($prelevement->notes as $noteType => $noteQualifie):
                                $defautsStr = "";
                                foreach ($noteQualifie->defauts as $key => $defaut):
                                    $defautsStr .= $defaut;
                                    if ($key != count($noteQualifie->defauts) - 1):
                                        $defautsStr .= ', ';
                                    endif;
                                endforeach;
                                ?>
                                <li>
                                    <span class="<?php echo ($noteQualifie->isMauvaiseNote()) ? "bg-danger text-danger" : ""; ?>">
                                    <?php
                                    echo DegustationClient::$note_type_libelles[$noteType] . ' : <strong class="pull-right">' . (($noteQualifie->note == "X") ? "Non dégusté" : $noteQualifie->note) . '</strong>';
                                    if (count($noteQualifie->defauts)):
                                        echo "<br/><small class='text-muted'>" . $defautsStr . "</small>";
                                    else:
                                       echo "<br/><small class='text-muted'>-</small>";
                                    endif;
                                    ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <em class="text-muted"><?php echo $prelevement->appreciations; ?></em></td>
                        <td>N° <?php echo $prelevement->anonymat_degustation; ?><br /><?php if($prelevement->anonymat_prelevement_complet): ?>N° <?php echo $prelevement->anonymat_prelevement_complet; ?><?php endif; ?></td>
                        <td class="text-center"><?php if ($prelevement->exist('type_courrier') && $prelevement->type_courrier): ?>
                            <a href="<?php echo url_for('degustation_courrier_prelevement', $prelevement) ?>">
                            <?php endif; ?>
                            <?php echo getTypeCourrier($prelevement); ?>
                            <?php if ($prelevement->exist('type_courrier') && $prelevement->type_courrier): ?>
                            </a>
                        <?php else: ?>
                            <em>Non défini</em>
                        <?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <em>Aucun prélèvement dégusté pour cet opérateur</em>
        <?php endif; ?>
    </div>
</div>
