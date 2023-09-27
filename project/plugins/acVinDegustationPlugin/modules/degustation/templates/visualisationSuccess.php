<?php use_helper('Lot'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>

<div class="page-header no-border">
  <h2>
    <?php if ($degustation->getType() === DegustationClient::TYPE_MODEL): ?>
        Dégustation
    <?php else: ?>
        Tournée
    <?php endif; ?>
    du
    <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?>
    <small><?php echo $degustation->getLieuNom(); ?></small>
  </h2>
</div>

<h4>Lots prélevés (<?php echo count($preleves->getRawValue(), COUNT_RECURSIVE) - count(array_keys($preleves->getRawValue())) ?>)</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Information sur le lot</th>
            <th>Adresse de logement</th>
            <th>Date de prélèvement</th>
            <th>Affection</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($preleves) < 1): ?>
        <tr><td colspan=4 class='text-center'>Aucun lot prélevé</td></tr>
    <?php endif ?>
    <?php foreach ($preleves as $etablissement_id => $operateur): ?>
        <tr class="active">
            <td colspan=4>
                <a href="<?php echo url_for('etablissement_visualisation', ['identifiant' => $etablissement_id]) ?>">
                    <strong><?php echo $operateur[0]->declarant_nom ?></strong>
                </a>
            </td>
        </tr>
        <?php foreach ($operateur as $lot): ?>
        <tr>
            <td>
                <?php echo showOnlyProduit($lot); ?><br/>
                Volume : <?php echo $lot->volume ?> <small class="text-muted">hl</small>
            </td>
            <td>
                <?php echo $lot->getAdresseLogement(); ?><br/>
                <small class='text-muted'>SECTEUR: <?php echo $lot->getSecteur(); ?></small><br/>
            </td>
            <td class='text-center'>
                <?php echo DateTimeImmutable::createFromFormat('Y-m-d', $lot->getPreleve())->format('d/m/Y'); ?>
            </td>
            <td style="display: flex; flex-flow: column wrap; align-items: center">
                <?php if ($lot->id_document_provenance): ?>
                    <small class="text-muted">
                        <a href="#"><?php echo $lot->id_document_provenance ?></a>
                    </small>
                    <span class="glyphicon glyphicon-chevron-down"></span>
                <?php endif ?>
                <strong class="text-bold">
                    <a href="#">Ce document</a>
                </strong>
                <?php if ($lot->id_document_affectation): ?>
                    <span class="glyphicon glyphicon-chevron-down">↓</span>
                    <small class="text-muted">
                        <a href="#"><?php echo $lot->id_document_affectation ?></a>
                    </small>
                <?php endif ?>
            </td>
        </tr>
        <?php endforeach ?>
    <?php endforeach ?>
    </tbody>
</table>

<h4>Lots à prélever (<?php echo count($aPreleves->getRawValue(), COUNT_RECURSIVE) - count(array_keys($aPreleves->getRawValue())) ?>)</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Information sur le lot</th>
            <th>Adresse de logement</th>
            <th>Date de prélèvement</th>
            <th>Affection</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($aPreleves) < 1): ?>
        <tr><td colspan=4 class='text-center'>Aucun lot à prélever</td></tr>
    <?php endif ?>
    <?php foreach ($aPreleves as $etablissement_id => $operateur): ?>
        <tr class="active">
            <td colspan=4>
                <a href="<?php echo url_for('etablissement_visualisation', ['identifiant' => $etablissement_id]) ?>">
                    <strong><?php echo $operateur[0]->declarant_nom ?></strong>
                </a>
            </td>
        </tr>
        <?php foreach ($operateur as $lot): ?>
        <tr>
            <td>
                <?php echo showOnlyProduit($lot); ?><br/>
                Volume : <?php echo $lot->volume ?> <small class="text-muted">hl</small>
            </td>
        </tr>
        <?php endforeach ?>
    <?php endforeach ?>
    </tbody>
</table>
