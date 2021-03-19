<?php echo '<?xml version="1.0" encoding="utf-8" ?>' ?>
<?php $regions = ($region)? sfConfig::get('app_oi_regions') : null;  ?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <Security xmlns="http://<?php  echo ($region)? $regions[$region]['domain_action'] :  sfConfig::get('app_oi_domain_action'); ?>/">
      <Login><?php echo ($region)? $regions[$region]['login'] : sfConfig::get('app_oi_login'); ?></Login>
      <Password><?php echo ($region)? $regions[$region]['mdp'] : sfConfig::get('app_oi_mdp'); ?></Password>
    </Security>
  </soap:Header>
  <soap:Body>
    <CreationDrev xmlns="http://<?php echo ($region)? $regions[$region]['domain_action'] : sfConfig::get('app_oi_domain_action'); ?>/">
      <drev xmlns="">
				<code_site value="SGV" />
				<code_extravitis value="<?php echo intval(substr($drev->identifiant, 0, -2)); ?>" />
				<numero_evv value="<?php echo $drev->declarant->cvi; ?>" />
				<rs value="<?php echo $drev->declarant->raison_sociale; ?>" />
				<millesime value="<?php echo $drev->periode; ?>" />
				<date_saisie value="<?php echo $drev->getDateValidation() ?>" />
				<lignes>
<?php foreach ($drev->declaration->getProduits($region) as $produit): ?>
	<?php if ($codeProduit = $produit->getConfig()->getCodeProduit()): ?>
					<ligne>
						<code_cvi_vin value="<?php echo $produit->getConfig()->getCodeDouane(); ?>" />
                        <libelle_produit value="<?php echo $produit->getLibelleComplet(); ?>" />
<?php if ($produit->denomination_complementaire): ?>
                        <mention_valorisante value="<?php echo $produit->denomination_complementaire; ?>" />
<?php endif; ?>
						<code_syndicat_vin value="<?php echo $codeProduit; ?>" />
						<surface value="<?php echo $produit->superficie_revendique; ?>" />
						<volume value="<?php echo $produit->volume_revendique_total * 100; ?>" />
            <?php if($produit->hasVci()): ?>
            <vci_complement value="<?php echo floatval($produit->vci->complement); ?>" />
            <vci_nouveau value="<?php echo floatval($produit->vci->constitue); ?>" />
            <vci_rafraichi value="<?php echo floatval($produit->vci->rafraichi); ?>" />
            <vci_substitue value="<?php echo floatval($produit->vci->substitution); ?>" />
            <vci_detruit value="<?php echo floatval($produit->vci->destruction); ?>" />
            <vci_stock_n value="<?php echo floatval($produit->vci->stock_final); ?>"/>
            <vci_stock_n_1 value="<?php echo floatval($produit->vci->stock_precedent); ?>"/>
          <?php endif; ?>
          <?php if($produit->hasReserveInterpro()): ?>
              <vsi value="<?php echo floatval($produit->getVolumeReserveInterpro()); ?>" />
          <?php endif; ?>
  					</ligne>
	<?php endif; ?>
<?php endforeach; ?>
				</lignes>
			</drev>
		</CreationDrev>
  </soap:Body>
</soap:Envelope>
