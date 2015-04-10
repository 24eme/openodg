<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<div class="row">    
    <div class="col-xs-12">        
        
        <table class="table table-striped">

            <tr>
                <th>Date/heure</th>            
                <th>Action</th> 
            </tr>
            <?php foreach ($tournee->getTournees() as $date_compteid => $t):  ?>
             <tr>
                 <td><?php if(str_replace("-", "", $t->date)) { echo ucfirst(format_date($t->date, "P", "fr_FR")); }; ?></td>
                 <td><a href="<?php echo url_for('degustation_tournee', array('sf_subject' => $tournee, 'agent' => $t->id_agent, 'date' => $t->date)); ?>">
                     <?php echo "Tournée ".$t->nom_agent." (".count($t->operateurs)." visites)"; ?></a></td> 
            </tr>
            <?php endforeach; ?>
            <tr>
                <td><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR"))." à ".Date::francizeHeure($tournee->heure); ?></td>
                <td>
                    <a href="<?php if($tournee->statut == TourneeClient::STATUT_AFFECTATION): ?><?php echo url_for('degustation_affectation', $tournee); ?><?php elseif(in_array($tournee->statut, array(TourneeClient::STATUT_DEGUSTATIONS, TourneeClient::STATUT_COURRIERS, TourneeClient::STATUT_TERMINE))): ?><?php echo url_for('degustation_degustations', $tournee); ?><?php endif; ?>"><?php echo "Dégustation (".$tournee->nombre_commissions." commissions, ".count($tournee->degustateurs->PORTEUR_MEMOIRES)." porteurs de mémoires, ".count($tournee->degustateurs->TECHNICIEN_PRODUIT)." techniciens du produit, ".count($tournee->degustateurs->USAGER_PRODUIT)." usagers du produit)"; ?></a>
                    <a class="btn btn-xs btn-warning" href="<?php echo url_for('degustation_degustateurs_presence', $tournee) ?>"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;Présences des dégustateurs</a>
                </td> 
            </tr>
        </table>
    </div>
</div>