<?php

class sfWidgetDatalist extends sfWidgetForm
{
    public function configure($options = [], $attributes = [])
    {
        $this->addOption('list', false);
        $this->addOption('choices', []);
    }

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        if ($this->getOption('list')) {
            $name = $this->getOption('list');
        }

        $dom = new DOMDocument;
        $datalist = $dom->createElement('datalist');
        $datalist->setAttribute('id', $this->generateId($name));
        $dom->appendChild($datalist);

        foreach ($this->getOption('choices') as $option) {
            $o = $dom->createElement('option');
            $o->setAttribute('value', $option);

            $datalist->appendChild($o);
        }

        return $dom->saveHTML();
    }

    public function generateId($name, $value = null)
    {
        $name = parent::generateId($name, $value);

        if (strpos($name, '_') === false) {
            return $name;
        }

        $split = explode('_', $name);
        return end($split);
    }
}
