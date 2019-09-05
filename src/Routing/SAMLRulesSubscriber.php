<?php
/**
 * @file
 * Contains \Drupal\sso_roles\Routing\SSORouteSubscriber.
 */

namespace Drupal\sso_roles\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class SSORouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Change the default path of our user login to the SAML login if that option
    // is configured to do so.
    $config = \Drupal::config('sso_roles.settings');
    //if ($route = $collection->get('user.login')) {
    //  $route->setPath($config->get('saml_login_path'));
    //}
  }

}
