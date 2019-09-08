<?php

/**
 * @file
 * Contains \Drupal\saml_rules\EventSubscriber\SAMLRulesAnonymousLogin.
 */

namespace Drupal\saml_rules\EventSubscriber;

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
  }

  public function checkAuthStatus(GetResponseEvent $event) {
    $config = \Drupal::config('saml_rules.settings');
    $redirect = $config->get('require_auth');

    if (
      $this->account->isAnonymous() &&
      \Drupal::routeMatch()->getRouteName() != 'user.login' &&
      \Drupal::routeMatch()->getRouteName() != 'user.reset.login' &&
      !empty($redirect)) {

      // add logic to check other routes you want available to anonymous users,
      // otherwise, redirect to login page.
      $route_name = \Drupal::routeMatch()->getRouteName();
      //if (strpos($route_name, 'view') === 0 && strpos($route_name, 'rest_') !== FALSE) {
      //  return;
      //}

      $response = new RedirectResponse(\Drupal::url('user.login'));
      $response->send();
    }
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkAuthStatus');
    return $events;
  }

}