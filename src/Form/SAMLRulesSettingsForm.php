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
    $saml_account_management_url = ($config->get('saml_account_management_url') != NULL) ? $config->get('saml_account_management_url') : '';

    $form['saml_login_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SAML login URL path'),
      '#description' => $this->t('The URL the user will use to login via the SAML service provider.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $saml_login_path,
      '#required' => TRUE,
      '#weight' => 10,
    ];
    $form['saml_account_management_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SAML account management URL path'),
      '#description' => $this->t('The URL the user will use to manage their account at the SAML service provider. Leave this blank to use the default user login page. <b>NOTE: If you do not specify a login redirect page on the <i>samlauth</i> module and provide a URL here, then when a user logs in they will be taken to the page you specify in this field.</b>'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $saml_account_management_url,
      '#required' => FALSE,
      '#weight' => 15,
    ];
    $form['require_auth'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require authentication'),
      '#description' => $this->t('Require the user be authenticates in order to access web site.'),
      '#default_value' => $config->get('require_auth'),
      '#weight' => 20,
    ];
    $form['redirect_all'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Redirect login page to SAML SSO'),
      '#description' => $this->t('Redirects Drupal login page to the login page for the SSO service (SAML login URL path). Note that redirecting will cause all account management url\'s such as forgot password to redirect to the account management URL specified above.'),
      '#default_value' => $config->get('redirect_all'),
      '#weight' => 30,
      '#attributes' => [
        'id' => 'field_redirect_all',
      ],
    ];
    $form['redirect_register'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Redirect account registration page to SAML SSO'),
      '#description' => $this->t('Redirects Drupal account registration page to the login page for the SSO service (SAML login URL path). Note that redirecting will cause all account management url\'s such as forgot password to redirect to the account management URL specified above.'),
      '#default_value' => $config->get('redirect_register'),
      '#weight' => 35,
      '#attributes' => [
        'id' => 'field_redirect_register',
      ],
    ];
    $form['drupal_login'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Provide alternative url for Drupal Login'),
      '#description' => $this->t('Enable the standard Drupal login at a custom path for development or other purposes.'),
      '#default_value' => $config->get('drupal_login'),
      '#weight' => 40,
      '#attributes' => [
        'id' => 'field_drupal_login',
      ],
      '#states' => [
        'visible' => [
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
      '#weight' => 50,
      '#states' => [
        'visible' => [
          ':input[id="field_drupal_login"]' => ['checked' => TRUE],
          ':input[id="field_redirect_all"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[id="field_drupal_login"]' => ['checked' => TRUE],
          ':input[id="field_redirect_all"]' => ['checked' => TRUE],
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
      ->set('redirect_register', $form_state->getValue('redirect_register'))
      ->save();
    $this->config('saml_rules.settings')
      ->set('saml_account_management_url', $form_state->getValue('saml_account_management_url'))
      ->save();
    $this->config('saml_rules.settings')
      ->set('drupal_login', $form_state->getValue('drupal_login'))
      ->save();
    $this->config('saml_rules.settings')
      ->set('drupal_login_path', $form_state->getValue('drupal_login_path'))
      ->save();
    \Drupal::service('router.builder')->rebuild();
  }
}
