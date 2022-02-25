<?php use_helper('Lot') ?>
<?php use_helper('Date') ?>
Madame, Monsieur,

Au vu des documents fournis, et des résultats du contrôle documentaire, analytique et organoleptique, nous vous prions de bien vouloir trouver ci-dessous le résultat de vos lots prélevés pour la séance de dégustation du <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")) ?>.

<?php if(count($lotsConformes)): ?>
<?php if(count($lotsNonConformes) > 0 && count($lotsConformes) == 1): ?>1 de vos lots est CONFORME et APTE à la commercialisation<?php elseif(count($lotsNonConformes)): ?><?= count($lotsConformes) ?> de vos lots sont CONFORMES et APTES à la commercialisation<?php elseif(count($lotsConformes) == 1): ?>Votre lot est CONFORME et APTE à la commercialisation<?php else: ?>Vos <?= count($lotsConformes) ?> lots sont CONFORMES et APTES à la commercialisation<?php endif; ?>, vous pouvez télécharger le courrier en cliquant sur ce lien : <a href="<?php echo url_for('degustation_get_courrier_auth_conforme', [
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
    'identifiant' => $identifiant
], true) ?>"><?php echo url_for('degustation_get_courrier_auth_conforme_raccourci', [
    'id' => str_replace("DEGUSTATION-", "", $degustation->_id),
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
    'identifiant' => $identifiant
], true) ?></a>
<?php endif; ?>

<?php if(count($lotsNonConformes)): ?>
<?= count($lotsNonConformes) ?> <?php if(count($lotsNonConformes) == 1): ?>lot est NON CONFORME et BLOQUÉ<?php else: ?>lots sont NON CONFORMES et BLOQUÉS<?php endif; ?> à la commercialisation, vous pouvez télécharger <?php if(count($lotsNonConformes) > 1): ?>les courriers de chacun de ces lots<?php else: ?>le courrier de ce lot<?php endif ?> :

<?php foreach($lotsNonConformes as $lotNonConforme): ?>
- <?= trim(preg_replace("/[ ]+/", " ", (str_replace("&nbsp;", " ", strip_tags(showProduitCepagesLot($lotNonConforme)))))) . ", NON CONFORMITÉ de type " . $lotNonConforme->getShortLibelleConformite() ?> : <a href="<?php echo url_for('degustation_get_courrier_auth_nonconforme_raccourci', array(
    'id' => str_replace("DEGUSTATION-", "", $degustation->_id),
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $lotNonConforme->numero_dossier.$lotNonConforme->numero_archive),
    'lot_dossier' => $lotNonConforme->numero_dossier,
    'lot_archive' => $lotNonConforme->numero_archive
), true); ?>"><?php echo url_for('degustation_get_courrier_auth_nonconforme_raccourci', array(
    'id' => str_replace("DEGUSTATION-", "", $degustation->_id),
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $lotNonConforme->numero_dossier.$lotNonConforme->numero_archive),
    'lot_dossier' => $lotNonConforme->numero_dossier,
    'lot_archive' => $lotNonConforme->numero_archive
), true); ?></a>
<?php endforeach; ?>

Si vous souhaitez déclasser un de ces lots, vous pouvez le faire en ligne : <a href="<?php echo url_for('chgtdenom_lots', [
    'identifiant' => $identifiant,
    'campagne' => $degustation->campagne
], true) ?>"><?php echo url_for('chgtdenom_lots', [
    'identifiant' => $identifiant,
    'campagne' => $degustation->campagne
], true) ?></a>

<?php endif; ?>
Nous vous prions de croire, Madame, Monsieur, en l’expression de nos sentiments les meilleurs.
