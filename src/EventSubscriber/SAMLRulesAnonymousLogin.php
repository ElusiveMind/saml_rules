<?php

/**
 * @file
 * Contains \Drupal\saml_rules\EventSubscriber\SAMLRulesAnonymousLogin.
 */

namespace Drupal\saml_rules\EventSubscriber;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber subscribing to KernelEvents::REQUEST.
 */
class SAMLRulesAnonymousLogin implements EventSubscriberInterface {

  public function __construct() {
    $this->account = \Drupal::currentUser();
    \Drupal::service('page_cache_kill_switch')->trigger();
  }

  public function checkAuthStatus(GetResponseEvent $event) {
    $config = \Drupal::config('saml_rules.settings');
    $redirect = $config->get('require_auth');

    if (
      $this->account->isAnonymous() &&
      \Drupal::routeMatch()->getRouteName() != 'saml_rules.drupal_login' &&
      \Drupal::routeMatch()->getRouteName() != 'user.login' &&
      \Drupal::routeMatch()->getRouteName() != 'user.reset.login' &&
      \Drupal::routeMatch()->getRouteName() != 'samlauth.saml_controller_login' &&
      \Drupal::routeMatch()->getRouteName() != 'samlauth.saml_controller_acs' &&
      !empty($redirect)) {

      // TODO: add logic to check other routes you want available to anonymous users,
      // otherwise, redirect to login page. Admin panel to add these paths.
      //$route_name = \Drupal::routeMatch()->getRouteName();

      $response = new RedirectResponse(Url::fromRoute('user.login')->toString());
      $response->send();
    }

    // If we're trying to log out and we need not be allowed to log out, then we need
    // to log back in immediately. TODO: Redirect to the SAML SSO Logout link.
    if (
      !$this->account->isAnonymous() &&
      \Drupal::routeMatch()->getRouteName() == 'user.login' &&
      !empty($redirect)) {
        $response = new RedirectResponse(Url::fromRoute('user.login')->toString());
        $response->send();
      }
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkAuthStatus', 30);
    return $events;
  }

}