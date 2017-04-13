<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<div class="row">
    <div class="col-xs-12">

        <table class="table table-striped">

            <tr>
                <th>Date/heure</th>
                <th>Action</th>
            </tr>
            <tr>
                <td><?php if(str_replace("-", "", $tournee->date)) { echo ucfirst(format_date($tournee->date, "P", "fr_FR")); }; ?></td>
                <td><a href="<?php echo url_for("degustation_saisie", $tournee) ?>">Saisie</a></td>
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
                    <a class="btn btn-xs btn-warning" href="<?php echo url_for('degustation_degustateurs_presence', $tournee) ?>"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;Présences des dégustateurs</a>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</div>
