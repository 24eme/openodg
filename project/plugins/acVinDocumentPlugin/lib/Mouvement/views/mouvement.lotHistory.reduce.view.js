function(keys,values,rereduce) {
  if (!rereduce) {
    values = values.map(function(value){return value.document_ordre + value.statut ; });
  }
  values.sort().reverse();
  return values[0];
}

