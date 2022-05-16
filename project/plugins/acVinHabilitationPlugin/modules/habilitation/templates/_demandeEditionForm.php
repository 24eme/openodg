<?php use_helper('Orthographe'); ?>
<div class="modal fade modal-page modal-demande" aria-labelledby="Modifier la demande" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
                <div class="modal-header">
                    <a href="<?php echo url_for("habilitation_declarant", $demande->getDocument()->getEtablissementChais()) ?>" class="close" aria-hidden="true">&times;</a>
                    <h4 class="modal-title" id="myModalLabel">Demande <?php echo elision("de", strtolower($demande->getDemandeLibelle())) ?></h4>
					<?php echo $demande->libelle ?>
                </div>
                <div class="modal-body" style="padding-bottom: 0;">
					<table class="table table-condensed table-bordered table-striped">
					    <thead>
					        <tr>
					            <th class="col-xs-1">Date</th>
					            <th class="col-xs-3">Statut</th>
					            <th class="col-xs-9">Commentaire</th>
					        </tr>
					    </thead>
					    <tbody>
							<?php $historique = $demande->getFullHistorique(); ?>
							<?php foreach($historique as $key => $event): ?>
					        <tr style="<?php if($demande->date == $event->date && $demande->statut == $event->statut): ?>font-weight: bold;<?php endif; ?>">
					            <td><?php echo Date::francizeDate($event->date); ?></td>

					            <td><?php echo HabilitationClient::getInstance()->getDemandeStatutLibelle($event->statut); ?></td>

					            <td>
									<?php echo $event->commentaire; ?>
									<?php if($sf_user->isAdmin()): ?>
									<form style="display:inline;" id="form_commentaire_<?php echo $key ?>" action="<?php echo url_for('habilitation_demande_commentaire_modification', array('identifiant' => $demande->getDocument()->identifiant, 'demande' => $demande->getKey(), 'date' => $event->date, 'statut' => $event->statut)) ?>" method="post">
										<input type="hidden" name="commentaire" value="<?php echo sfWidget::escapeOnce($event->commentaire); ?>" />
										<button type="submit" onclick="var input = form.querySelector('#form_commentaire_<?php echo $key ?> input'); var value = prompt('Modification du commentaire', input.value); if(value) { input.value = value; return true; } return false;" class="btn btn-link btn-xs transparence-md"><span class="glyphicon glyphicon-pencil"></span></button>
									</form>
									<?php if(count($historique) > 1 && $demande->date == $event->date && $demande->statut == $event->statut): ?><a onclick="return confirm('Étes-vous sûr de vouloire supprimer ce statut ?')" class="btn btn-link pull-right btn-xs transparence-md" href="<?php echo url_for('habilitation_demande_suppression_derniere', array('identifiant' => $demande->getDocument()->identifiant, 'demande' => $demande->getKey(), 'date' => $demande->date, 'statut' => $demande->statut)) ?>"><span class="glyphicon glyphicon-remove"></span></a><?php endif; ?>
									<?php endif; ?>
								</td>
					        </tr>
							<?php endforeach; ?>
					    </tbody>
					</table>

					<?php if($sf_user->getFlash('info')): ?>
					<div class="alert alert-info">
						<?php echo $sf_user->getFlash('info'); ?>
					</div>
					<?php endif; ?>
				</div>
			<form method="post" action="" role="form" class="form-horizontal">
				<?php if($form instanceof sfForm): ?>
				<hr style="margin-top: 0; margin-bottom: 0;" />
				<div class="modal-body">
					<?php include_partial('habilitation/demandeForm', array('form' => $form, 'demande' => $demande)); ?>
				</div>
				<?php endif; ?>
                <div class="modal-footer">
					<?php if($form instanceof sfForm): ?>
<<<<<<< HEAD
<<<<<<< HEAD
                    <a class="btn btn-default pull-left" href="<?php echo (isset($urlRetour) && $urlRetour) ? $urlRetour : url_for("habilitation_declarant", $etablissement) ?>">Annuler</a>
                    <button type="submit" class="btn btn-success pull-right">Valider le changement</button>
					<?php else: ?>
						<a class="btn btn-default" href="<?php echo (isset($urlRetour) && $urlRetour) ? $urlRetour : url_for("habilitation_declarant", $etablissement) ?>">Fermer</a>
=======
                    <a class="btn btn-default pull-left" href="<?php echo (isset($urlRetour) && $urlRetour) ? $urlRetour : url_for("habilitation_declarant", $demande->getDocument()->getEtablissementChais()) ?>">Annuler</a>
                    <button type="submit" class="btn btn-success pull-right">Valider le changement</button>
					<?php else: ?>
						<a class="btn btn-default" href="<?php echo (isset($urlRetour) && $urlRetour) ? $urlRetour : url_for("habilitation_declarant", $demande->getDocument()->getEtablissementChais()) ?>">Fermer</a>
>>>>>>> 3de80e3d11 (Globalisation des demandes et éditions de ces dernieres)
=======
                    <a class="btn btn-default pull-left" href="<?php echo (isset($urlRetour) && $urlRetour) ? $urlRetour : url_for("habilitation_declarant", $form->getEtablissementChais()) ?>">Annuler</a>
                    <button type="submit" class="btn btn-success pull-right">Valider le changement</button>
					<?php else: ?>
						<a class="btn btn-default" href="<?php echo (isset($urlRetour) && $urlRetour) ? $urlRetour : url_for("habilitation_declarant", $form->getEtablissementChais()) ?>">Fermer</a>
>>>>>>> 1766c1f33e (Gestion d'autres de cas d'habilitation sans chais)
					<?php endif; ?>
				</div>
            </form>
        </div>
	</div>
</div>
