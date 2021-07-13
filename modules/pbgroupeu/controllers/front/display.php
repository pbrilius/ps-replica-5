<?php

class pbgroupeudisplayModuleFrontController extends ModuleFrontController
{
  public function initContent()
  {
    parent::initContent();
    $this->setTemplate('module:pbgroupeu/views/templates/front/display.tpl');
  }
}

 ?>
