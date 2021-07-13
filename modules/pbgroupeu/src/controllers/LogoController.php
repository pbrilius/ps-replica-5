<?php

namespace PBGroupeu\Controller;

use Doctrine\Common\Cache\CacheProvider;

class LogoController extends FrameworkBundleAdminController
{
  private $cache;

  public function __construct(CacheProvider $cache)
  {
    $this->cache = $cache;
  }

  public function displayAction()
  {
    return $this->render('@Modules/pbgroupeu/templates/admin/display.html.twig', [
      'current-logo' => Configuration::get('PBGROUPEU_FILE')
    ]);
  }
}
 ?>
