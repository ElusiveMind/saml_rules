<?php

namespace Drupal\saml_rules\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SAMLRulesSettingsForm.
 */
class SAMLRulesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'saml_rules.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'saml_rules_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('saml_rules.settings');
    $saml_login_path = ($config->get('saml_login_path') != NULL) ? $config->get('saml_login_path') : '/saml/login';

    $form['saml_login_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SAML Login Path'),
      '#description' => $this->t('The URL the user will use to login via the SAML service provider.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $saml_login_path,
      '#required' => TRUE,
      '#weight' => 1,
    ];
    $form['require_auth'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require Authentication'),
      '#description' => $this->t('Require the user be authenticates in order to access web site.'),
      '#default_value' => $config->get('require_auth'),
      '#weight' => 2,
    ];
    $form['redirect_all'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Redirect Login Page to SAML'),
      '#description' => $this->t('Redirects Drupal login page to the login page for the SSL (SSO Login Path)'),
      '#default_value' => $config->get('redirect_all'),
      '#weight' => 3,
      '#attributes' => [
        'id' => 'field_redirect_all',
      ],
    ];

    $form['drupal_login'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Provide alternative url for Drupal Login'),
      '#description' => $this->t('Enable the standard Drupal login at a custom path for development or other purposes.'),
      '#default_value' => $config->get('drupal_login'),
      '#weight' => 4,
      '#attributes' => [
        'id' => 'field_drupal_login',
      ],
      '#states' => [
        'visible' => [
          ':input[id="field_redirect_all"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[id="field_redirect_all"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['drupal_login_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom Drupal login path'),
      '#description' => $this->t('The custom path (no preceding or trailing slash) to the standard Drupal login.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('drupal_login_path'),
      '#weight' => 5,
      '#states' => [
        'visible' => [
          ':input[id="field_drupal_login"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[id="field_drupal_login"]' => ['checked' => TRUE],
        ],
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('saml_rules.settings')
      ->set('saml_login_path', $form_state->getValue('saml_login_path'))
      ->save();
    $this->config('saml_rules.settings')
      ->set('require_auth', $form_state->getValue('require_auth'))
      ->save();
    $this->config('saml_rules.settings')
      ->set('redirect_all', $form_state->getValue('redirect_all'))
      ->save();
    $this->config('saml_rules.settings')
      ->set('drupal_login', $form_state->getValue('drupal_login'))
      ->save();
    $this->config('saml_rules.settings')
      ->set('drupal_login_path', $form_state->getValue('drupal_login_path'))
      ->save();
    drupal_flush_all_caches();
  }
}
