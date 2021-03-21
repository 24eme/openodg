function(keys,values,rereduce) {
  iMax = 0;
  for (i = 1 ; i < values.length ; i++) {
    if (values[i].document_ordre + values[i].statut > values[iMax].document_ordre + values[iMax].statut) {
      iMax = i;
    }
  }
   return values[iMax];
 }
 
