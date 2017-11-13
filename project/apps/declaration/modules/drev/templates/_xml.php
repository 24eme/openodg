<?php echo '<?xml version="1.0" encoding="utf-8" ?>' ?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <Security xmlns="http://<?php echo sfConfig::get('app_oi_domain_action'); ?>/">
      <Login><?php echo sfConfig::get('app_oi_login'); ?></Login>
      <Password><?php echo sfConfig::get('app_oi_mdp'); ?></Password>
    </Security>
  </soap:Header>
  <soap:Body>
    <CreationDrev xmlns="http://<?php echo sfConfig::get('app_oi_domain_action'); ?>/">
      <drev xmlns="">
				<code_site value="SGV" />
				<code_extravitis value="<?php echo intval(substr($drev->identifiant, 0, -2)); ?>" />
				<numero_evv value="<?php echo $drev->declarant->cvi; ?>" />
				<rs value="<?php echo $drev->declarant->raison_sociale; ?>" />
				<millesime value="<?php echo $drev->campagne; ?>" />
				<date_saisie value="<?php echo $drev->getDateValidation() ?>" />
				<lignes>
<?php foreach ($drev->declaration->getProduits() as $produit): ?>
					<ligne>
						<code_cvi_vin value="<?php echo $produit->getConfig()->getCodeDouane(); ?>" />
						<libelle_produit value="<?php echo $produit->getLibelleComplet(); ?>" />
						<code_syndicat_vin value="<?php echo $produit->getCodeCouleur(); ?>" />
						<surface value="<?php echo $produit->superficie_revendique; ?>" />
						<volume value="<?php echo $produit->volume_revendique_total; ?>" />
						<vsi value="0" />
					</ligne>
<?php endforeach; ?>
				</lignes>
			</drev>
		</CreationDrev>
  </soap:Body>
</soap:Envelope>
