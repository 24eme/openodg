<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>

        <h2>Informations <?php if(!$tournee->getNbTournees()): ?><a href="" class="btn btn-xs btn-default-step">Modifier</a><?php endif; ?></h2>
        <table class="table table-condensed table-striped">
            <tr>
                <td class="col-xs-3"><strong>Date de la dégustation</strong></td>
                <td class="col-xs-9"><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></td>
            </tr>
            <tr>
                <td class="col-xs-3"><strong>Appellation</strong></td>
                <td class="col-xs-9"><?php echo $tournee->getLibelle() ?></td>
            </tr>
            <tr>
                <td class="col-xs-3"><strong>Demande de prélèvement</strong></td>
                <td class="col-xs-9"><?php if($tournee->date_prelevement_debut): ?><?php echo getDatesPrelevements($tournee); ?><?php else: ?><em>Aucune</em><?php endif; ?></td>
            </tr>
            <tr>
                <td class="col-xs-3"><strong>Opérateurs</strong></td>
                <td class="col-xs-9"><?php echo count($tournee->operateurs) ?></td>
            </tr>
            <tr>
                <td class="col-xs-3"><strong>Prélèvements</strong></td>
                <td class="col-xs-9"><?php echo $tournee->nombre_prelevements ?> vins (<?php echo $tournee->getNbLots() ?> lots prévus)</td>
            </tr>
            <tr>
                <td class="col-xs-3"><strong>Organisme</strong></td>
                <td class="col-xs-9"><?php echo $tournee->organisme ?></td>
            </tr>

        </table>

        <h2>Résumé des actions</h2>
        <table class="table table-condensed table-striped">
            <tr>
                <th class="col-xs-3">Date/heure</th>
                <th class="col-xs-9">Action</th>
            </tr>
            <?php foreach ($tournee->getTournees() as $date_compteid => $t):  ?>
             <tr>
                 <td><?php if(str_replace("-", "", $t->date)) { echo ucfirst(format_date($t->date, "P", "fr_FR")); }; ?></td>
                 <td><a href="<?php echo url_for('degustation_tournee', array('sf_subject' => $tournee, 'agent' => $t->id_agent, 'date' => $t->date)); ?>">
                     <?php echo "Tournée ".$t->nom_agent." (".count($t->operateurs)." visites)"; ?></a></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?> <?php if($tournee->heure): ?>à <?php echo Date::francizeHeure($tournee->heure); ?><?php endif; ?></td>
                <td>
                    <a href="<?php if($tournee->statut == TourneeClient::STATUT_AFFECTATION): ?><?php echo url_for('degustation_affectation', $tournee); ?><?php elseif(in_array($tournee->statut, array(TourneeClient::STATUT_DEGUSTATIONS, TourneeClient::STATUT_COURRIERS, TourneeClient::STATUT_TERMINE))): ?><?php echo url_for('degustation_degustations', $tournee); ?><?php endif; ?>">Dégustation (<?php if($tournee->nombre_commissions): ?><?php echo $tournee->nombre_commissions." commissions, " ?><?php endif; ?>
                    <?php if($tournee->degustateurs->exist('PORTEUR_MEMOIRES')): ?><?php echo count($tournee->degustateurs->PORTEUR_MEMOIRES)." porteurs de mémoires, " ?><?php endif; ?><?php if($tournee->degustateurs->exist('TECHNICIEN_PRODUIT')): ?><?php echo count($tournee->degustateurs->TECHNICIEN_PRODUIT)." techniciens du produit, " ?><?php endif; ?><?php if($tournee->degustateurs->exist('USAGER_PRODUIT')): ?><?php echo count($tournee->degustateurs->USAGER_PRODUIT)." usagers du produit" ?><?php endif; ?>)</a>
                    <?php if($tournee->statut != TourneeClient::STATUT_ORGANISATION): ?>
                    <a class="btn btn-xs btn-link" href="<?php echo url_for('degustation_degustateurs_presence', $tournee) ?>"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;Présences des dégustateurs</a>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
