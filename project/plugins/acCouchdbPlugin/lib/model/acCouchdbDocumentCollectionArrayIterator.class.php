<?php
class acCouchdbDocumentCollectionArrayIterator extends ArrayIterator {
    private $_doc_collection;

    public function __construct(acCouchdbCollection $doc_collection)
    {
        $this->_doc_collection = $doc_collection;
        parent::__construct($doc_collection->getDatas());
    }

    public function current(): mixed {
        return $this->_doc_collection->get($this->key());
    }

    public function offsetGet($index): mixed {
        return $this->_doc_collection->offsetGet($index);
    }

    public function offsetSet($index, $newval): void {
        $this->_doc_collection->offsetSet($index, $newval);
    }

    public function  offsetExists($index): bool {
        return $this->_doc_collection->offsetExists($index);
    }

    public function offsetUnset($index): void {
        $this->_doc_collection->offsetUnset($index);
    }
}
