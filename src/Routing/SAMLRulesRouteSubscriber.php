<?php
/**
 * @file
 * Contains \Drupal\saml_rules\Routing\SSORouteSubscriber.
 */

namespace Drupal\saml_rules\Routing;

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
    if (!empty($redirect_all)) {
      if ($route = $collection->get('user.login')) {
        $route->setPath($config->get('saml_login_path'));
      }
    }
  }

}
