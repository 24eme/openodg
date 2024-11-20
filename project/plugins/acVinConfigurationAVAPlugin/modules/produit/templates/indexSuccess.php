<ol class="breadcrumb">
    <li><a href="<?php echo url_for("produits") ?>">Produits</a></li>
    <li class="active"><a href="<?php echo url_for("produits", array('date' => $date)) ?>"><?php echo $date ?></a></li>
    <li class="text-muted"><?php echo $config->_id ?><small>@<?php echo $config->_rev ?></small></li>
</ol>

<h2>Facturation</h2>
<a href="<?php echo url_for('facturation_template_last'); ?>">Voir le template de facturation</a>

<h2>Dates d'ouverture des télédéclarations</h2>

<?php $teledeclarations = array(
    "ParcellaireManquantConfiguration" => "Pieds manquants",
    "DRevConfiguration" => "Revendication",
    "TravauxMarcConfiguration" => "Travaux de Marc",
    "DRevMarcConfiguration" => "Revendication de Marc",
    "PMCConfiguration" => "PMC",
    "ParcellaireAffectationConfiguration" => "Affectation Parcellaire",
    "ParcellaireAffectationCremantConfiguration" => "Affectation Parcellaire Crémant",
    "IntentionCremantConfiguration" => "Intention Cremant",
    "TirageConfiguration" => "Tirage",
); ?>
<table class="table table-bordered table-striped table-condensed">
    <thead>
        <tr>
            <th>Déclaration</th>
            <th class="col-xs-2 text-center">Mois de début de campagne</th>
            <th class="text-center">Date d'ouverture</th>
            <th class="text-center">Date de fermeture</th>
            <th class="text-center">État</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($teledeclarations as $classConfig => $libelle): ?>
        <?php if(class_exists($classConfig) && $classConfig::getInstance()->isModuleEnabled()): ?>
        <tr>
            <td><?php echo $libelle ?></td>
            <td class="text-center"><?php echo $classConfig::getInstance()->getCampagneDebutMois(); ?></td>
            <td class="text-center"><?php echo $classConfig::getInstance()->getDateOuvertureDebut(); ?></td>
            <td class="text-center"><?php echo $classConfig::getInstance()->getDateOuvertureFin(); ?></td>
            <td class="text-center <?php if($classConfig::getInstance()->isOpen()): ?>success text-success<?php else: ?>danger text-danger<?php endif; ?>"><?php if($classConfig::getInstance()->isOpen()): ?>Ouvert<?php else: ?>Fermé<?php endif; ?></td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Produits</h2>

<table class="table table-condensed table-striped table-bordered">
    <thead>

        <tr>
            <th rowspan="2" style="<?php if(!isset($notDisplayDroit)): ?>width:170px;<?php else: ?>width: 260px;<?php endif; ?>">Libellé</th>
            <th rowspan="2" style="width:30px;">Cat.</th>
            <th rowspan="2">Genre</th>
            <th rowspan="2">Dénom.</th>
            <th rowspan="2">Mention</th>
            <th rowspan="2">Lieu</th>
            <th rowspan="2">Couleur</th>
            <th rowspan="2">Cépage</th>
            <th rowspan="2" style="width:15px;" class="text-center" title="Réserve Interpro">RI</th>
            <th style="width:15px;" colspan="7" class="text-center">Rendement</th>
        </tr>
        <tr>
            <th style="width:15px;" class="text-center" title="Rendement DREV">DREV</th>
            <th style="width:15px;" class="text-center" title="Rendement VCI">VCI</th>
            <th style="width:15px;" class="text-center" title="Rendement VCI Total">VCI Tot.</th>
            <th style="width:15px;" class="text-center" title="Rendement VCI">VSI</th>
        </tr>

    </thead>
    <tbody>
        <?php foreach($produits as $produit): ?>

            <?php use_helper('Float') ?>
            <tr>
                <td class="center">
                <?php echo str_replace('/', '/ ', $produit->getLibelleComplet()); ?>
                </td>
                <td>
                    <?php $noeud = $produit->getCertification();  echo ($noeud->getLibelle()) ? $noeud->getLibelle() : sprintf("<span class='text-muted'>(%s)</span>", str_replace('certification', 'cert.', $noeud->getKey())) ?>
                </td>
                <td>
                    <?php $noeud = $produit->getGenre();  echo ($noeud->getLibelle()) ? $noeud->getLibelle() : sprintf("<span class='text-muted'>(%s)</span>", str_replace('genre', 'g.', $noeud->getKey())); ?>
                </td>
                <td>
                    <?php $noeud = $produit->getAppellation();  echo ($noeud->getLibelle()) ? $noeud->getLibelle() : sprintf("<span class='text-muted'>(%s)</span>", $noeud->getKey()) ?>
                </td>
                <td>
                    <?php $noeud = $produit->getMention();  echo ($noeud->getLibelle()) ? $noeud->getLibelle() : sprintf("<span class='text-muted'>(%s)</span>", str_replace('mention', 'ment°', $noeud->getKey())) ?>
                </td>
                <td>
                    <?php $noeud = $produit->getLieu();  echo ($noeud->getLibelle()) ? $noeud->getLibelle() : sprintf("<span class='text-muted'>(%s)</span>", $noeud->getKey()) ?>
                </td>
                <td>
                    <?php $noeud = $produit->getCouleur();  echo ($noeud->getLibelle()) ? $noeud->getLibelle() : sprintf("<span class='text-muted'>(%s)</span>", str_replace('couleur', 'coul.', $noeud->getKey())) ?>
                </td>
                <td>
                    <?php $noeud = $produit->getCepage();  echo ($noeud->getLibelle()) ? str_replace('/', '/ ', $noeud->getLibelle()) : sprintf("<span class='text-muted'>(%s)</span>", $noeud->getKey()) ?>
                </td>
                <td class="center">
                    <?php echo ($produit->getCodeDouane()) ? $produit->getCodeDouane() : "" ?>
                </td>
                <td class="text-right">
                    <?php echo sprintFloat($produit->getRendement()) ?>&nbsp;
                </td>
                <td class="text-right">
                    <?php echo sprintFloat($produit->getRendementVci()) ?>&nbsp;
                </td>
                <td class="text-right">
                    <?php echo sprintFloat($produit->getRendementVciTotal()) ?>&nbsp;
                </td>
                <td class="text-right">
                    <?php echo sprintFloat($produit->getRendementVsi()) ?>&nbsp;
                </td>
            </tr>

        <?php endforeach; ?>
    </tbody>
</table>
<?php if(class_exists("Parcellaire") && in_array('parcellaire', sfConfig::get('sf_enabled_modules'))): ?>
    <h2>Les Aires</h2>
    <table class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th class="col-xs-10">Dénomination libellé</th>
                <th class="col-xs-1 text-center">Identifiant INAO</th>
                <th class="col-xs-1 text-center">Couleur</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach(ParcellaireConfiguration::getInstance()->getAiresInfos() as $aire): ?>
                <tr>
                    <td><?php echo $aire['name'];  ?></td>
                    <td class="text-center"><a href="https://www.opendatawine.fr/denominations/<?php echo $aire['denomination_id']; ?>.html"><?php echo $aire['denomination_id']; ?></a></td>
                    <td class="text-center"><span style="background-color: <?php echo $aire['color']; ?>"> &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp; </span></td>
                </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
