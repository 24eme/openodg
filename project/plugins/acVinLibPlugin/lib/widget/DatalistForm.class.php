<?php

class DatalistForm
{
    /** format ['name' => ['item1', 'item2', ...]] */
    public $options = [];

    public function addDatalist($name, $options)
    {
        if (array_key_exists($name, $this->options)) {
            throw new sfException('Un datalist existe déjà avec ce nom');
        }

        $this->options[$name] = $options;
    }

    public function renderDatalist($name)
    {
        if (array_key_exists($name, $this->options) === false) {
            throw new sfException('Datalist inconnue : '.$name.'. Datalist configurées : '.implode(', ', array_keys($this->options)));
        }

        $dom = new DOMDocument;
        $datalist = $dom->createElement('datalist');
        $datalist->setAttribute('id', $name);
        $dom->appendChild($datalist);

        foreach ($this->options[$name] as $option) {
            $o = $dom->createElement('option');
            $o->setAttribute('value', $option);

            $datalist->appendChild($o);
        }

        return $dom->saveHTML();
    }
}
