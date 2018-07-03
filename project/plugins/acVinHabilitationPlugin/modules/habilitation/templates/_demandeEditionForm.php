<?php use_helper('Orthographe'); ?>
<div class="modal fade modal-page" aria-labelledby="Modifier la demande" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
            <form method="post" action="" role="form" class="form-horizontal">
                <div class="modal-header">
                    <a href="<?php echo url_for("habilitation_declarant", $etablissement) ?>" class="close" aria-hidden="true">&times;</a>
                    <h4 class="modal-title" id="myModalLabel">Demande <?php echo elision("de", strtolower($demande->getDemandeLibelle())) ?></h4>
					<?php echo $demande->libelle ?>
                </div>
                <div class="modal-body">
					<?php if(isset($demande)): ?>
					<table class="table table-condensed table-bordered table-striped">
					    <thead>
					        <tr>
					            <th class="col-xs-1">Date</th>
					            <th class="col-xs-3">Statut</th>
					            <th class="col-xs-9">Commentaire</th>
					        </tr>
					    </thead>
					    <tbody>
							<?php foreach($historique as $event): ?>
							<?php if(!preg_match("/".$demande->getKey()."/", $event->iddoc)): continue; endif; ?>
					        <tr style="<?php if($demande->date == $event->date && $demande->statut = $event->statut): ?>font-weight: bold;<?php endif; ?>">
					            <td><?php echo Date::francizeDate($event->date); ?></td>
					            <td><?php echo HabilitationClient::$demande_statut_libelles[$event->statut]; ?></td>
					            <td><?php echo $event->commentaire; ?></td>
					        </tr>
							<?php endforeach; ?>
					    </tbody>
					</table>
					<?php endif; ?>
                    <?php include_partial('habilitation/demandeForm', array('form' => $form, 'demande' => $demande)); ?>
    		    </div>
                <div class="modal-footer">
                    <a class="btn btn-default btn pull-left" href="<?php echo (isset($urlRetour) && $urlRetour) ? $urlRetour : url_for("habilitation_declarant", $etablissement) ?>">Annuler</a>
                    <button type="submit" class="btn btn-success btn pull-right">Valider le changement</button>
				</div>
            </form>
        </div>
	</div>
</div>
