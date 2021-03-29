<?php use_helper('Float') ?>
<tr>
	<td class="center">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getFormatLibelleDefinitionNoeud()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo $produit->getLibelleFormat() ?>
		</a>
	</td>
	<td>
		<?php include_partial('itemNoeud', array('produit' => $produit, 'noeud' => $produit->getCertification(), 'cvo' => $cvo)) ?>
	</td>
	<td>
		<?php include_partial('itemNoeud', array('produit' => $produit, 'noeud' => $produit->getGenre(), 'cvo' => $cvo)) ?>
	</td>
	<td>
		<?php include_partial('itemNoeud', array('produit' => $produit, 'noeud' => $produit->getAppellation(), 'cvo' => $cvo)) ?>
	</td>
	<td>
		<?php include_partial('itemNoeud', array('produit' => $produit, 'noeud' => $produit->getMention(), 'cvo' => $cvo)) ?>
	</td>
	<td>
		<?php include_partial('itemNoeud', array('produit' => $produit, 'noeud' => $produit->getLieu(), 'cvo' => $cvo)) ?>
	</td>
	<td>
		<?php include_partial('itemNoeud', array('produit' => $produit, 'noeud' => $produit->getCouleur(), 'cvo' => $cvo)) ?>
	</td>
	<td>
		<?php include_partial('itemNoeud', array('produit' => $produit, 'noeud' => $produit->getCepage(), 'cvo' => $cvo)) ?>
	</td>
    <td class="text-center">
        <a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getNoeudCepagesAutorises()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>"><?php echo count($produit->getCepagesAutorises()); ?></a>
    </td>
	<?php if(!isset($notDisplayDroit)): ?>
   	<td class="center">
     <?php if ($cvo) : ?>
     <strong title="<?php echo $cvo->date ?>"><?php echo $cvo->getStringTaux(); ?></strong>
     <?php else: ?>
     pas de CVO
     <?php endif; ?>
	</td>
	<td class="center">
		<strong><?php echo (!is_null($douane)) ? $douane->taux : null ?></strong>
	</td>
	<td class="center">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo ($produit->getCodeComptable()) ? $produit->getCodeComptable() : "(Aucun)" ?>
		</a>
	</td>
	<?php endif; ?>
	<td class="center">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo ($produit->getCodeDouane()) ? $produit->getCodeDouane() : "(Aucun)" ?>
		</a>
	</td>
	<td class="text-right">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getAppellation()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo sprintFloat($produit->getRendementReserveInterpro()) ?>&nbsp;
			<?php if ($produit->hasRendementReserveInterproMin()) echo " (>".sprintFloat($produit->getRendementReserveInterproMin()).")"; ?>&nbsp;
		</a>
	</td>
	<td class="text-right">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getAppellation()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo sprintFloat($produit->getRendementConseille()) ?>&nbsp;
		</a>
	</td>
	<td class="text-right">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getAppellation()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo sprintFloat($produit->getRendement()) ?>&nbsp;
		</a>
	</td>
	<td class="text-right">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getAppellation()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo sprintFloat($produit->getRendementDrL5()) ?>&nbsp;
		</a>
	</td>
    <td class="text-right">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getAppellation()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo sprintFloat($produit->getRendementDrL15()) ?>&nbsp;
		</a>
	</td>
	<td class="text-right">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getAppellation()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo sprintFloat($produit->getRendementVci()) ?>&nbsp;
		</a>
	</td>
	<td class="text-right">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getAppellation()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo sprintFloat($produit->getRendementVciTotal()) ?>&nbsp;
		</a>
	</td>
	<td class="text-right">
		<a href="<?php echo url_for('produit_modification', array('noeud' => $produit->getAppellation()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>">
			<?php echo $produit->code_produit; ?>&nbsp;
		</a>
	</td>
</tr>
