<?php use_helper('Lot') ?>
<?php use_helper('Date') ?>
Monsieur, Madame,

Nous vous prions de bien vouloir trouver ci-dessous extrait du procès verbal de la séance de dégustation du : <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")) ?>.

Au vu des documents fournis, et des résultats du contrôle documentaire, analytique et organoleptique, nous vous confirmons les résultats pour vos lots présentés.

<?php if(count($lotsConformes) > 0 && count($lotsNonConformes) > 0): ?>
Certain de vos lots sont CONFORMES et aptes à la commercialisation tandis que d'autres sont NON CONFORMES.
<?php elseif(count($lotsConformes) == 1): ?>
Votre lot est CONFORME et apte à la commercialisation.
<?php elseif(count($lotsConformes) > 1): ?>
L'ensemble de vos lots sont CONFORMES et aptes à la commercialisation.
<?php elseif(count($lotsNonConformes) == 1): ?>
Votre lot est NON CONFORME.
<?php elseif(count($lotsNonConformes) > 1): ?>
Vos lots sont NON CONFORMES.
<?php endif; ?>

<?php if(count($lotsConformes)): ?>
Vous trouverez ci-dessous le lien vers le courrier confirmant la conformité des lot(s) présenté(s) (<?= count($lotsConformes) ?>) : <a href="<?php echo url_for('degustation_get_courrier_auth', [
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $identifiant),
    'type' => 'Conformite',
    'identifiant' => $identifiant
], true) ?>">
<?php echo url_for('degustation_get_courrier_auth', [
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $identifiant),
    'type' => 'Conformite',
    'identifiant' => $identifiant
], true) ?>
</a>

<?php endif; ?>
<?php if(count($lotsConformes) && count($lotsNonConformes)): ?>
Par ailleurs, certains de vos vins dont la liste figure dans les fiches de non conformité ci-jointes ont été ajournés.

<?php endif; ?>
<?php if(count($lotsNonConformes)): ?>
Vous trouverez en cliquant sur les liens ci-dessous le courrier concernant chacun des lots présentant une non conformité :
<?php foreach($lotsNonConformes as $lotNonConforme): ?>
* <?= showProduitLot($lotNonConforme) . ", non conformité de type : " . $lotNonConforme->getShortLibelleConformite() ?>
<a href="<?php echo url_for('degustation_get_courrier_auth', array(
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $lotNonConforme->numero_dossier.$lotNonConforme->numero_archive),
    'type' => 'NonConformite',
    'lot_dossier' => $lotNonConforme->numero_dossier,
    'lot_archive' => $lotNonConforme->numero_archive
), true); ?>">
<?php echo url_for('degustation_get_courrier_auth', array(
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $lotNonConforme->numero_dossier.$lotNonConforme->numero_archive),
    'type' => 'NonConformite',
    'lot_dossier' => $lotNonConforme->numero_dossier,
    'lot_archive' => $lotNonConforme->numero_archive
), true); ?>
</a>
<?php endforeach; ?>

<?php endif; ?>
Nous vous prions de croire, Monsieur, Madame, en l’expression de nos sentiments les meilleurs.
