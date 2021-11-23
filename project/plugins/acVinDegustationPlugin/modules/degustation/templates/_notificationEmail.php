<?php use_helper('Lot') ?>
<?php use_helper('Date') ?>
Madame, Monsieur,

Nous vous prions de bien vouloir trouver ci-dessous extrait du procès verbal de la séance de dégustation du : <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")) ?>.

Au vu des documents fournis, et des résultats du contrôle documentaire, analytique et organoleptique, nous vous confirmons les résultats pour vos lots prélevés.
<?php if(count($lotsConformes)): ?>

<?php if(count($lotsNonConformes) > 0 && count($lotsConformes) == 1): ?>1 de vos lots est CONFORME et apte à la commercialisation.<?php elseif(count($lotsNonConformes)): ?><?= count($lotsConformes) ?> de vos lots sont CONFORMES et aptes à la commercialisation.<?php elseif(count($lotsConformes) == 1): ?>Votre lot est CONFORME et apte à la commercialisation.<?php else: ?>Vos <?= count($lotsConformes) ?> lots sont CONFORMES et aptes à la commercialisation.<?php endif; ?>


Vous trouverez ci-dessous le lien vers le courrier confirmant la conformité <?php if(count($lotsConformes) > 1): ?>des lots présentés<?php else: ?>du lot présenté<?php endif; ?> : <a href="<?php echo url_for('degustation_get_courrier_auth_conforme', [
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
    'identifiant' => $identifiant
], true) ?>">%3C<?php echo url_for('degustation_get_courrier_auth_conforme', [
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
    'identifiant' => $identifiant
], true) ?>%3E</a>
<?php endif; ?>

<?php if(count($lotsNonConformes)): ?>
<?= count($lotsNonConformes) ?> <?php if(count($lotsNonConformes) == 1): ?>lot est NON CONFORME et bloqué<?php else: ?>lots sont NON CONFORMES et bloqués<?php endif; ?> à la commercialisation.

Vous trouverez en cliquant sur le<?php if(count($lotsNonConformes) > 1): ?>s<?php endif; ?> lien<?php if(count($lotsNonConformes) > 1): ?>s<?php endif; ?> ci-dessous le courrier concernant <?php if(count($lotsNonConformes) == 1): ?>le lot<?php else: ?>les lots<?php endif; ?> présentant une non conformité :

<?php foreach($lotsNonConformes as $lotNonConforme): ?>
- <?= showProduitCepagesLot($lotNonConforme) . ", NON CONFORMITÉ de type " . $lotNonConforme->getShortLibelleConformite() ?> : <a href="<?php echo url_for('degustation_get_courrier_auth_nonconforme', array(
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $lotNonConforme->numero_dossier.$lotNonConforme->numero_archive),
    'lot_dossier' => $lotNonConforme->numero_dossier,
    'lot_archive' => $lotNonConforme->numero_archive
), true); ?>">%3C<?php echo url_for('degustation_get_courrier_auth_nonconforme', array(
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $lotNonConforme->numero_dossier.$lotNonConforme->numero_archive),
    'lot_dossier' => $lotNonConforme->numero_dossier,
    'lot_archive' => $lotNonConforme->numero_archive
), true); ?>%3E</a>
<?php endforeach; ?>

Si vous décidez de déclasser, vous pouvez télécharger la déclaration de changement de dénomination (DICD) : <a href="<?php echo url_for('chgtdenom_lots', [
    'identifiant' => $identifiant,
    'campagne' => $degustation->campagne
], true) ?>">%3C<?php echo url_for('chgtdenom_lots', [
    'identifiant' => $identifiant,
    'campagne' => $degustation->campagne
], true) ?>%3E</a>

<?php endif; ?>
Nous vous prions de croire, Madame, Monsieur, en l’expression de nos sentiments les meilleurs.
