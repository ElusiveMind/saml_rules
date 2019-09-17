<?php

namespace Drupal\saml_rules\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SAMLRulesManageSAMLAccount.
 */
class SAMLRulesManageSAMLAccount extends ControllerBase {

 /**
   * redirect().
   */
  public function redirect() {
    $config = $this->config('saml_rules.settings');
    $saml_account_management_url = $config->get('saml_account_management_url');
    $response = new RedirectResponse($saml_account_management_url);
    $response->send();
  }
}
