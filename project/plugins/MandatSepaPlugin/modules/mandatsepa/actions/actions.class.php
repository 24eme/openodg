<?php

class mandatsepaActions extends sfActions
{

	public function executePdf(sfWebRequest $request) {
		$mandatSepa = $this->getRoute()->getMandatSepa();
		$this->document = new MandatSepaPDF($mandatSepa, $request->getParameter('output','pdf'), false);
    $this->document->setPartialFunction(array($this, 'getPartial'));
    if ($request->getParameter('force')) {
        $this->document->removeCache();
    }
    $this->document->generate();
    $this->document->addHeaders($this->getResponse());
    return $this->renderText($this->document->output());
	}

}
