<?php use_helper('Lot') ?>
<?php use_helper('Date') ?>
Bonjour,

Suite au contrôle de votre exploitation, vous pouvez télécharger le document suivant : <a href="<?php echo url_for('controle_pdf_auth', [ 'id' => $controle->_id,
    'auth' => UrlSecurity::generateAuthKey($controle->_id)
], true) ?>"><?php echo url_for('controle_pdf_auth', [
    'id' => $controle->_id,
    'auth' => UrlSecurity::generateAuthKey($controle->_id)
], true) ?></a>

Le potentiel de production devra être respecté pour la prochaine récolte.

Bien cordialement,
<br/>
<?php if($agent): ?>
<?php echo $agent->prenom .'&nbsp;'. $agent->nom ."\n"?>
<i><?php echo $agent->fonction; ?></i>
Mob. : <?php echo $agent->getTelephoneMobile() ."\n"; ?>
Tel. : <?php echo $agent->getTelephoneBureau() ."\n"; ?>
<a href="<?php echo $agent->getSiteInternet(); ?>">syndicat-cotesdeprovence.com</a>
<?php endif; ?>
