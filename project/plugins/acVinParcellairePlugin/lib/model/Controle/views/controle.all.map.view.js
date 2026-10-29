function (doc) {
  if (doc.type !== "Controle") {
    return;
  }

  emit([null, doc.date_tournee, doc.agent_identifiant, doc.identifiant], doc);
  emit([doc.mouvements_statuts[0][2], doc.date_tournee, doc.agent_identifiant, doc.identifiant], doc);
}
