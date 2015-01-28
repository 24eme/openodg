<?php include_partial('degustation/step', array('active' => 'validation')); ?>

<div class="page-header no-border">
    <h2>Validation</h2>
</div>

<h3>Points bloquants</h3>
<div class="alert alert-danger" role="alert">
    <ul>
        <li>Le dégustateur XXX 1 ne possède pas d'email</li>
        <li>Le dégustateur XXX 2 ne possède pas d'email</li>
    </ul>
</div>

<h3>Points de vigilance</h3>
<div class="alert alert-warning" role="alert">
    <ul>
        <li>Le dégustateur XXX 1 a été séléctionné à la fois dans le collège Porteur de mémoire et Technicien du produit</li>
    </ul>
</div>


<div class="row">
    <div class="col-xs-6">
        <h3>Prélèvements</h3>
        <table class="table table-striped">
            <tr>
                <th>Date de fin de prélevment</th>            
                <td>2 février 2014</td>            
            </tr>
            <tr>
                <th>Produits</th>            
                <td>AOC Alsace</td>            
            </tr>
            <tr>
                <th>Nombre d'opérateurs</th>            
                <td>50</td>            
            </tr>
        </table>
    </div>
    <div class="col-xs-6">
        <h3>Dégustation</h3>
        <table class="table table-striped">
            <tr>
                <th>Nombre de commissions</th>            
                <td>4</td>            
            </tr>
            <tr>
                <th>Date et heure</th>            
                <td>20 février 2014 à 10h00</td>            
            </tr>
            <tr>
                <th>Nombre de lots à déguster</th>            
                <td>70</td>            
            </tr>
            <tr>
                <th>Techniciens du produit</th>            
                <td>10</td>            
            </tr>
            <tr>
                <th>Porteurs de mémoire</th>            
                <td>2</td>            
            </tr>
            <tr>
                <th>Usagés du produit</th>            
                <td>1</td>            
            </tr>
        </table>
    </div>
</div>
<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_prelevements') ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <a href="<?php echo url_for('degustation_validation') ?>" class="btn btn-default btn-lg btn-upper">Valider</a>
    </div>
</div>

<?php include_partial('drev/popupConfirmationValidation'); ?>