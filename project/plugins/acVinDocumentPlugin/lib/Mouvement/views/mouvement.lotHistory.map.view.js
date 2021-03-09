function (doc) {
  if (!doc.mouvements_lots) {
    return;
  }
  for(identifiant in doc.mouvements_lots) {
    for(key in doc.mouvements_lots[identifiant]) {
      mouvement = doc.mouvements_lots[identifiant][key];

      emit([
        mouvement.declarant_identifiant,
        mouvement.numero_dossier,
        mouvement.numero_archive,
        mouvement.doc_ordre,
        mouvement.statut,
        mouvement.origine_document_id
      ], mouvement);
    }
  }
}
