<?php
/**
 * @file
 * Contains \Drupal\saml_rules\Routing\SAMLRulesRouteSubscriber.
 */

namespace Drupal\saml_rules\Routing;

use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class SAMLRulesRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Change the default path of our user login to the SAML login if that option
    // is configured to do so.
    $config = \Drupal::config('saml_rules.settings');
    $redirect_all = $config->get('redirect_all');
    $redirect_all = $config->get('redirect_register');
    $saml_account_management_url = $config->get('saml_account_management_url');

    if (!empty($redirect_all)) {
      if ($route = $collection->get('user.login')) {
        $route->setPath($config->get('saml_login_path'));
      }
    }
    if (!empty($redirect_register)) {
      if ($route = $collection->get('user.register')) {
        $route->setPath($config->get('saml_login_path'));
      }
    }

    if (!empty($redirect_register) || !empty($redirect_all)) {
      if (!empty($saml_account_management_url)) {
        if ($route = $collection->get('user.page')) {
          $route->setPath('/saml/manage');
        }
        if ($route = $collection->get('user.pass')) {
          $route->setPath('/saml/manage');
        }
        if ($route = $collection->get('user.pass.http')) {
          $route->setPath('/saml/manage');
        }
      }
    }

    // If we are configured to redirect our login page to saml and still want our
    // Drupal login (for dev purposes) then configure that here.
    $drupal_login = $config->get('drupal_login');
    if (!empty($drupal_login)) {
      $drupal_login_path = $config->get('drupal_login_path');
      $route = new Route($drupal_login_path,
        [
          '_title' => 'Log in',
          '_form' => '\Drupal\user\Form\UserLoginForm',
        ],
        [
          '_user_is_logged_in' => 'FALSE',
        ],
        [
          '_maintenance_access' => 'TRUE',
        ]
      );
      $collection->add('saml_rules.drupal_login', $route);
    }

    // If we are configured to redirect our login page to saml and still want our
    // Drupal login (for dev purposes) then configure that here.
    $saml_account_management_url = $config->get('saml_account_management_url');
    if (!empty($saml_account_management_url)) {
      $route = new Route('/saml/manage',
        [
          '_title' => 'Manage SAML account',
          '_controller' => '\Drupal\saml_rules\Controller\SAMLRulesManageSAMLAccount::redirectAccountManagement',
        ],
        [
          '_user_is_logged_in' => 'TRUE',
        ],
        [
          '_maintenance_access' => 'TRUE',
        ]
      );
      $collection->add('saml_rules.manage_account', $route);
    }
  }

}
