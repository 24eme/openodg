function (doc) {
  if (!doc.mouvements_lots) {
    return;
  }
  for(identifiant in doc.mouvements_lots) {
    for(key in doc.mouvements_lots[identifiant]) {
      mouvement = doc.mouvements_lots[identifiant][key];
      for(const region of mouvement.region.split('|')) {
        emit([
          region,
          mouvement.declarant_identifiant,
          mouvement.campagne,
          mouvement.numero_dossier,
          mouvement.numero_archive,
          mouvement.document_ordre,
          mouvement.statut,
          mouvement.document_id,
          mouvement.unique_id
        ], mouvement);
      }

      emit([
        null,
        mouvement.declarant_identifiant,
        mouvement.campagne,
        mouvement.numero_dossier,
        mouvement.numero_archive,
        mouvement.document_ordre,
        mouvement.statut,
        mouvement.document_id,
        mouvement.unique_id
      ], mouvement);
    }
  }
}
