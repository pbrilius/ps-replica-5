<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

define('_PS_MODE_DEV_', true);

class pbgroupeu extends Module
{
  public $tabs= [
    'name' => 'Image Stock - pbgroupeu',
    'class_name' => 'Logo',
    'visible' => true,
    'parent_class_name' => 'ShopParameters',
  ];

  public function __construct()
  {
    $this->name = 'pbgroupeu';
    $this->tab = 'image_tag_on_stock_item';
    $this->author = 'Povilas Brilius';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = [
      'min' => '1.6',
      'max' => _PS_VERSION_
    ];
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('pbgroupeu');
    $this->description = $this->l('Auto managing image tags on stock items');

    $this->confirmUninstall = $this->l('Are you sure to uninstall this package?');

    if (!Configuration::get('PBGROUPEU_NAME')) {
      $this->warning = $this->l('No name provided');
    }
  }

  public function install()
  {
    if (Shop::isFeatureActive()) {
      Shop::setContext(Shop::CONTEXT_ALL);
    }

    if (!parent::install() ||
      !$this->registerHook('leftColumn') ||
      !$this->registerHook('actionFrontControllerSetMedia') ||
      !$this->registerHook('header') ||
      !Configuration::updateValue('PBGROUPEU_NAME', ' pbgroupeu')
    ) {
      return false;
    }

    return true;
  }

  public function uninstall()
  {
    if (!parent::uninstall() ||
      !Configuration::deleteByName('PBGROUPEU_NAME')
    ) {
      return false;
    }

    return true;
  }

  public function getContent()
  {
    $output = null;

    if (Tools::isSubmit('submit'.$this->name)) {
        $fileName = strval(Tools::getValue('PBGROUPEU_FILE'));

        if (
            !$fileName ||
            empty($fileName) ||
            !Validate::isGenericName($fileName)
        ) {
            $output .= $this->displayError($this->l('Invalid Stock Image value'));
        } else {
            Configuration::updateValue('PBGROUPEU_FILE', $fileName);
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
    }

    return $output.$this->displayForm();
  }

  public function displayForm()
  {
      // Get default language
      $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

      // Init Fields form array
      $fieldsForm[0]['form'] = [
          'legend' => [
              'title' => $this->l('Settings'),
          ],
          'input' => [
              [
                  'type' => 'file',
                  'label' => $this->l('Stock Image value'),
                  'name' => 'PBGROUPEU_FILE',
                  'required' => true
              ]
          ],
          'submit' => [
              'title' => $this->l('Save'),
              'class' => 'btn btn-default pull-right'
          ]
      ];

      $helper = new HelperForm();

      // Module, token and currentIndex
      $helper->module = $this;
      $helper->name_controller = $this->name;
      $helper->token = Tools::getAdminTokenLite('AdminModules');
      $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

      // Language
      $helper->default_form_language = $defaultLang;
      $helper->allow_employee_form_lang = $defaultLang;

      // Title and toolbar
      $helper->title = $this->displayName;
      $helper->show_toolbar = true;        // false -> remove toolbar
      $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
      $helper->submit_action = 'submit'.$this->name;
      $helper->toolbar_btn = [
          'save' => [
              'desc' => $this->l('Save'),
              'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
              '&token='.Tools::getAdminTokenLite('AdminModules'),
          ],
          'back' => [
              'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
              'desc' => $this->l('Back to list')
          ]
      ];

      // Load current value
      $helper->fields_value['PBGROUPEU_FILE'] = Tools::getValue('PBGROUPEU_FILE', Configuration::get('PBGROUPEU_FILE'));

      return $helper->generateForm($fieldsForm);
  }

  public function hookDisplayLeftColumn($params)
  {
    $this->context->smarty->assign([
      'pbgroupeu_name' => Configuration::get('PBGROUPEU_NAME'),
      'pbgroupeu_link' => $this->context->link->getModuleLink('pbgroupeu', 'display'),
      'pbgroupeu_message' => $this->l('This is a simple text message'),
    ]);

    return $this->display(__FILE__, 'pbgroupeu.tpl');
  }

  public function hookDisplayRightColumn($params)
  {
    return $this->hookDisplayLeftColumn($params);
  }

  public function hookActionFrontControllerSetMedia()
  {
    $this->context->controller->registerStylesheet(
      'pbgroupeu-style',
      $this->_path . 'views/css/pbgroupeu.css',
      [
        'media' => 'all',
        'priority' => 1000,
      ]
    );

    $this->context->controller->registerJavascript(
      'pbgroupeu-javascript',
      $this->_path . 'views/js/pbgroupeu.js',
      [
        'position' => 'bottom',
        'priority' => 1000,
      ]
    );
  }

  public function hookDisplayHeader($params)
  {
    return $this->hookDisplayLeftColumn($params);
  }
}
