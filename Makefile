couchurl=project/config/databases.yml

all: .views/etablissements.json .views/societe.json .views/compte.json .views/declaration.json .views/piece.json .views/generation.json .views/habilitation.json .views/archivage.json .views/mouvement.json

.views/societe.json: project/plugins/acVinSocietePlugin/lib/model/views/societe.all.reduce.view.js project/plugins/acVinSocietePlugin/lib/model/views/societe.all.map.view.js project/plugins/acVinSocietePlugin/lib/model/views/societe.export.map.view.js .views
	perl bin/generate_views.pl $(couchurl) project/plugins/acVinSocietePlugin/lib/model/views/societe.all.reduce.view.js project/plugins/acVinSocietePlugin/lib/model/views/societe.all.map.view.js project/plugins/acVinSocietePlugin/lib/model/views/societe.export.map.view.js > $@ || rm >@

.views/etablissements.json:  project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.findByCvi.reduce.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.region.reduce.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.all.map.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.findByCvi.map.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.all.reduce.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.douane.reduce.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.region.map.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.douane.map.view.js .views
		perl bin/generate_views.pl $(couchurl) project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.findByCvi.reduce.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.region.reduce.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.all.map.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.findByCvi.map.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.all.reduce.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.douane.reduce.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.region.map.view.js project/plugins/acVinEtablissementPlugin/lib/model/views/etablissement.douane.map.view.js  > $@ || rm >@

.views/compte.json: project/plugins/acVinComptePlugin/lib/model/views/compte.all.reduce.view.js project/plugins/acVinComptePlugin/lib/model/views/compte.all.map.view.js  project/plugins/acVinComptePlugin/lib/model/views/compte.tags.reduce.view.js project/plugins/acVinComptePlugin/lib/model/views/compte.tags.map.view.js project/plugins/acVinComptePlugin/lib/model/views/compte.login.reduce.view.js project/plugins/acVinComptePlugin/lib/model/views/compte.login.map.view.js .views
	perl bin/generate_views.pl $(couchurl) project/plugins/acVinComptePlugin/lib/model/views/compte.all.reduce.view.js project/plugins/acVinComptePlugin/lib/model/views/compte.all.map.view.js project/plugins/acVinComptePlugin/lib/model/views/compte.tags.reduce.view.js project/plugins/acVinComptePlugin/lib/model/views/compte.tags.map.view.js project/plugins/acVinComptePlugin/lib/model/views/compte.login.reduce.view.js project/plugins/acVinComptePlugin/lib/model/views/compte.login.map.view.js > $@ || rm >@

.views/declaration.json: project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.tous.map.view.js project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.tous.reduce.view.js project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.identifiant.map.view.js project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.identifiant.reduce.view.js 	project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.export.reduce.view.js  project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.export.map.view.js .views
	perl bin/generate_views.pl $(couchurl) project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.tous.reduce.view.js project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.tous.map.view.js project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.export.reduce.view.js project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.export.map.view.js project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.identifiant.reduce.view.js 	project/plugins/DeclarationPlugin/lib/Declaration/view/declaration.identifiant.map.view.js  > $@ || rm >@

.views/piece.json: project/plugins/acVinDocumentPlugin/lib/Piece/views/piece.all.map.view.js project/plugins/acVinDocumentPlugin/lib/Piece/views/piece.all.reduce.view.js .views
		perl bin/generate_views.pl $(couchurl) project/plugins/acVinDocumentPlugin/lib/Piece/views/piece.all.reduce.view.js  project/plugins/acVinDocumentPlugin/lib/Piece/views/piece.all.map.view.js > $@ || rm >@

.views/generation.json: project/plugins/acVinGenerationPlugin/lib/model/views/generation.history.reduce.view.js project/plugins/acVinGenerationPlugin/lib/model/views/generation.history.map.view.js project/plugins/acVinGenerationPlugin/lib/model/views/generation.creation.reduce.view.js project/plugins/acVinGenerationPlugin/lib/model/views/generation.creation.map.view.js
		perl bin/generate_views.pl $(couchurl) project/plugins/acVinGenerationPlugin/lib/model/views/generation.history.reduce.view.js project/plugins/acVinGenerationPlugin/lib/model/views/generation.history.map.view.js project/plugins/acVinGenerationPlugin/lib/model/views/generation.creation.reduce.view.js project/plugins/acVinGenerationPlugin/lib/model/views/generation.creation.map.view.js > $@ || rm >@

.views/habilitation.json: project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.activites.reduce.view.js project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.activites.map.view.js project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.demandes.reduce.view.js project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.demandes.map.view.js project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.historique.reduce.view.js project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.historique.map.view.js
		perl bin/generate_views.pl $(couchurl) project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.activites.reduce.view.js  project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.activites.map.view.js project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.demandes.reduce.view.js project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.demandes.map.view.js 		project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.historique.reduce.view.js project/plugins/acVinHabilitationPlugin/lib/model/Habilitation/views/habilitation.historique.map.view.js  > $@ || rm >@

.views/archivage.json: project/plugins/acVinDocumentPlugin/lib/Archivage/views/archivage.all.map.view.js project/plugins/acVinDocumentPlugin/lib/Archivage/views/archivage.all.reduce.view.js
	perl bin/generate_views.pl $(couchurl) project/plugins/acVinDocumentPlugin/lib/Archivage/views/archivage.all.map.view.js project/plugins/acVinDocumentPlugin/lib/Archivage/views/archivage.all.reduce.view.js > $@ || rm >@

.views/mouvement.json: project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.consultation.reduce.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.facture.map.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.consultation.map.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.facture.reduce.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.lot.map.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.lotHistory.map.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.lotHistory.reduce.view.js
	perl bin/generate_views.pl $(couchurl) project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.consultation.reduce.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.facture.map.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.consultation.map.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.facture.reduce.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.lot.map.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.lotHistory.map.view.js project/plugins/acVinDocumentPlugin/lib/Mouvement/views/mouvement.lotHistory.reduce.view.js  > $@ || rm >@

.views:
	mkdir -p .views

clean:
	rm -f .views/*; mkdir -p .views
