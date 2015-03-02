<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<div class="row">    
    <div class="col-xs-12">        
        
        <table class="table table-striped">

            <tr>
                <th>Date/heure</th>            
                <th>Action</th> 
            </tr>
            <?php foreach ($degustation->getTournees() as $date_compteid => $tournee):  ?>
             <tr>
                 <td><?php echo format_date($tournee->date, "D", "fr_FR")." journée"; ?></td>
                 <td><a href="<?php echo url_for('degustation_tournee', array('id' => $degustation->_id, 'agent' => $tournee->id_agent, 'date' => $tournee->date)); ?>">
                     <?php echo "Tournée ".$tournee->nom_agent." (".count($tournee->prelevements)." visites)"; ?></a></td> 
            </tr>
            <?php endforeach; ?>
            <tr>
                <td><?php echo format_date($degustation->date, "D", "fr_FR")." à ".Date::francizeHeure($degustation->heure); ?></td>
                <td><?php echo "Dégustation (".$degustation->nombre_commissions." commissions, ".count($degustation->degustateurs->PORTEUR_MEMOIRES)." porteurs de mémoires, ".count($degustation->degustateurs->TECHNICIEN_PRODUIT)." techniciens du produit, ".count($degustation->degustateurs->USAGER_PRODUIT)." usagers du produit)"; ?></td> 
            </tr>
        </table>
    </div>
</div>