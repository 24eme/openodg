function(doc) {
    if (!doc.date) {
        return;
    }
    emit([doc.type, doc.date], doc);
}