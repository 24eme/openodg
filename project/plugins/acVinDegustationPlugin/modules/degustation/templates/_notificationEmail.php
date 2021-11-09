<?php use_helper('Lot') ?>
<?php use_helper('Date') ?>
Madame, Monsieur,

Nous vous prions de bien vouloir trouver ci-dessous extrait du procès verbal de la séance de dégustation du : <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")) ?>.

Au vu des documents fournis, et des résultats du contrôle documentaire, analytique et organoleptique, nous vous confirmons les résultats pour vos lots prélevés.

<?php if(count($lotsConformes) > 0 && count($lotsNonConformes) > 0): ?>
Certains de vos lots sont :
<?php elseif(count($lotsConformes) == 1 || count($lotsNonConformes) == 1): ?>
Votre lot est :
<?php elseif(count($lotsConformes) > 1 || count($lotsNonConformes) > 1): ?>
L'ensemble de vos lots sont :
<?php endif; ?>

<?php if(count($lotsConformes)): ?>
- Conforme(s) et apte(s) à la commercialisation. Vous trouverez ci-dessous le lien vers le courrier confirmant la conformité du(des) lot(s) présenté(s) (<?= count($lotsConformes) ?>) :
<a href="<?php echo url_for('degustation_get_courrier_auth_conforme', [
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
    'identifiant' => $identifiant
], true) ?>"><?php echo url_for('degustation_get_courrier_auth_conforme', [
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
    'identifiant' => $identifiant
], true) ?>
</a>

<?php endif; ?>
<?php if(count($lotsNonConformes)): ?>
- Non conforme(s) et bloqué(s) à la commercialisation. Vous trouverez en cliquant sur le(s) lien(s) ci-dessous le courrier concernant chacun des lots présentant une non conformité :

<?php foreach($lotsNonConformes as $lotNonConforme): ?>
    <?= showProduitCepagesLot($lotNonConforme) . ", NON CONFORMITÉ de type " . $lotNonConforme->getShortLibelleConformite() ?> : <a href="<?php echo url_for('degustation_get_courrier_auth_nonconforme', array(
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $lotNonConforme->numero_dossier.$lotNonConforme->numero_archive),
    'lot_dossier' => $lotNonConforme->numero_dossier,
    'lot_archive' => $lotNonConforme->numero_archive
), true); ?>"><?php echo url_for('degustation_get_courrier_auth_nonconforme', array(
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $lotNonConforme->numero_dossier.$lotNonConforme->numero_archive),
    'lot_dossier' => $lotNonConforme->numero_dossier,
    'lot_archive' => $lotNonConforme->numero_archive
), true); ?></a>

<?php endforeach; ?>

Si vous décidez de déclasser, vous pouvez télécharger la déclaration de changement de dénomination (DICD) :
<a href="<?php echo url_for('chgtdenom_lots', [
    'identifiant' => $identifiant,
    'campagne' => $degustation->campagne
], true) ?>"><?php echo url_for('chgtdenom_lots', [
    'identifiant' => $identifiant,
    'campagne' => $degustation->campagne
], true) ?>
</a>

<?php endif; ?>
Nous vous prions de croire, Madame, Monsieur, en l’expression de nos sentiments les meilleurs.
