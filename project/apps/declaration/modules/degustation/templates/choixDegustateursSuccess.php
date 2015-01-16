<div class="page-header">
    <h2>Choix des dégustateurs</h2>
</div>

<form action="" method="post" class="form-horizontal">
    
    <div class="row">
        <div class="col-xs-12">
            <h3>Porteur de mémoire</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="col-xs-6">Nom</th>
                        <th class="col-xs-3 small">Dernière Venue</th>
                        <th class="col-xs-3 small">Formation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>M Clément FEND</td>
                        <td class="small">13 mois</td>
                        <td class="small">2014</td>
                    </tr>
                    <tr>
                        <td>M Franck MITTNACHT</td>
                        <td class="small">9 mois</td>
                        <td class="small">2012</td>
                    </tr>
                    <tr>
                        <td>M Christophe RIEFLE</td>
                        <td class="small">8 mois</td>
                        <td class="small">2013</td>
                    </tr>
                    <tr>
                        <td>M Paul FUCHS</td>
                        <td class="small">3 mois</td>
                        <td class="small">2014</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-12">
        <h3>Technicien du produit</h3>
        <div class="col-xs-6">
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="col-xs-6">Nom</th>
                        <th class="col-xs-3">Dernière Venue</th>
                        <th class="col-xs-3">Formation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>M Clément FEND</td>
                        <td>13 mois</td>
                        <td>2014</td>
                    </tr>
                    <tr>
                        <td>M Franck MITTNACHT</td>
                        <td>9 mois</td>
                        <td>2012</td>
                    </tr>
                    <tr>
                        <td>M Christophe RIEFLE</td>
                        <td>8 mois</td>
                        <td>2013</td>
                    </tr>
                    <tr>
                        <td>M Paul FUCHS</td>
                        <td>3 mois</td>
                        <td>2014</td>
                    </tr>
                    <tr>
                        <td>M Clément FEND</td>
                        <td>13 mois</td>
                        <td>2014</td>
                    </tr>
                    <tr>
                        <td>M Franck MITTNACHT</td>
                        <td>9 mois</td>
                        <td>2012</td>
                    </tr>
                    <tr>
                        <td>M Christophe RIEFLE</td>
                        <td>8 mois</td>
                        <td>2013</td>
                    </tr>
                    <tr>
                        <td>M Paul FUCHS</td>
                        <td>3 mois</td>
                        <td>2014</td>
                    </tr>
                    <tr>
                        <td>M Clément FEND</td>
                        <td>13 mois</td>
                        <td>2014</td>
                    </tr>
                    <tr>
                        <td>M Franck MITTNACHT</td>
                        <td>9 mois</td>
                        <td>2012</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-6">
            <select data-placeholder="Sélectionner" class="form-control select2 select2-offscreen select2autocomplete">
                <option selected="selected"></option>
                <?php for($i; $i < 150; $i++): ?>
                <option>Monsieur X</option>
                <?php endfor; ?>
            </select>
        </div>
        </div>
        <div class="col-xs-12">
            <h3>Usagers du produit</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="col-xs-6">Nom</th>
                        <th class="col-xs-3">Dernière Venue</th>
                        <th class="col-xs-3">Formation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>M Clément FEND</td>
                        <td>13 mois</td>
                        <td>2014</td>
                    </tr>
                    <tr>
                        <td>M Franck MITTNACHT</td>
                        <td>9 mois</td>
                        <td>2012</td>
                    </tr>
                    <tr>
                        <td>M Christophe RIEFLE</td>
                        <td>8 mois</td>
                        <td>2013</td>
                    </tr>
                    <tr>
                        <td>M Paul FUCHS</td>
                        <td>3 mois</td>
                        <td>2014</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation_degustation') ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
            <a href="" class="btn btn-default btn-lg btn-upper">Continuer</a>
        </div>
    </div>

    
</form>
