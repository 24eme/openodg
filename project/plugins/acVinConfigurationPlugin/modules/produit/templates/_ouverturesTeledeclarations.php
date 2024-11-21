<h2>Dates d'ouverture des télédéclarations <a href="#ouvertures" name="ouvertures" class="small"><span class="glyphicon glyphicon-link"></span></a></h2>
<?php $teledeclarations = array(
    "ParcellaireManquantConfiguration" => "Pieds manquants",
    "DRevConfiguration" => "Revendication",
    "TravauxMarcConfiguration" => "Travaux de Marc",
    "DRevMarcConfiguration" => "Revendication de Marc",
    "PMCConfiguration" => "PMC",
    "ConditionnementConfiguration" => "Conditionnement",
    "TransactionConfiguration" => "Transaction",
    "ChgtDenomConfiguration" => "Changement de dénomination",
    "ParcellaireIrrigableConfiguration" => "Irrigation",
    "ParcellaireIrrigueConfiguration" => "Irrigue",
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
