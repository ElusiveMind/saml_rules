<?php

namespace Drupal\saml_rules\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SAMLRulesAuthenticationRuleForm.
 */
class SAMLRulesAuthenticationRuleForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'saml_rules.authentication_rules',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'saml_rules_authentication_rules_form';
  }

 
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $rule_name = NULL) {
    $config = $this->config('saml_rules.authentication_rules');
    // Set up our settings form for this particular account (new or update)
    if (!empty($rule_name)) {
      $rules = $config->get('rules');
      $rule = $rules[$rule_name];
      $form['rule_name'] = [
        '#type' => 'hidden',
        '#value' => $rule_name,
      ];
      $form['rule_update'] = [
        '#type' => 'hidden',
        '#value' => 1,
      ];
      $saml_attribute = $rule[''];
      $saml_value = $rule['saml_value'];

      // Actions: assign roles, update email, 
      $action = $rule['action'];
      $roles = $rule['roles'];
      $email = $rule['email'];
    }
    else {
      $saml_attribute = $saml_value = $action = $email = NULL;
      $roles = ['authenticated' => 'authenticated'];
    }

    $form['authentication_rules'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('SAML Rules: Authentication Rule'),
    );
    $form['authentication_rules']['saml_attribute'] = array(
      '#type' => 'textfield',
      '#title' => t('Incoming SAML attribute to evaluate'),
      '#description' => $this->t('The incoming attribute (variable) name from the SAML service. Note that these are case sensitive.'),
      '#max_length' => 128,
      '#required' => TRUE,
      '#default_value' => $saml_attribute,
    );
    $form['authentication_rules']['saml_value'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('SAML value to check for'),
      '#description' => $this->t('The value to check for in the SAML Attribute.'),
      '#max_length' => 255,
      '#required' => TRUE,
      '#default_value' => $saml_value,
    );
    $form['authentication_rules']['action'] = array(
      '#type' => 'select',
      '#title' => $this->t('Type of action to take'),
      '#options' => [
        'roles' => $this->t('Assign roles to account'),
        'email' => $this->t('Alter email address depending on incoming value.'),
      ],
      '#required' => TRUE,
      '#default_value' => $action,
      '#attributes' => [
        'name' => 'field_select_action',
      ],
    );
    $form['authentication_rules']['roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Roles to be assigned.'),
      '#description' => $this->t('The roles to be assigned based on the criteria above. The "authenticated user" role should always be assigned.'),
      '#options' => array_map(['\Drupal\Component\Utility\Html', 'escape'], user_role_names(TRUE)),
      '#default_value' => $roles,
      '#states' => [
        'visible' => [
          ':input[name="field_select_action"]' => ['value' => 'roles'],
        ],
      ],
    );
    $form['authentication_rules']['email'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Change email address value.'),
      '#description' => $this->t('Alter the email adress. Use SAML attribute values by placing the attribute name in brackets ([]).'),
      '#max_length' => 255,
      '#required' => TRUE,
      '#default_value' => $email,
      '#states' => [
        'visible' => [
          ':input[name="field_select_action"]' => ['value' => 'email'],
        ],
      ],
    );

    $form = parent::buildForm($form, $form_state);
    return $form;
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
    
    $values = $form_state->getValues();
    $config = $this->config('tweet_feed.twitter_accounts');
    $accounts = $config->get('accounts');
    $account_machine_name = preg_replace('/[^a-z0-9]+/', '_', strtolower($values['account_name']));

    if (empty($values['account_update']) && !empty($accounts[$account_machine_name])) {
      $suffix = 1;
      do {
        $new_account_machine_name = $account_machine_name . '_' . $suffix;
        $suffix++;
      }
      while (!empty($accounts[$new_account_machine_name]));
      $account_machine_name = $new_account_machine_name;
    }
    
    if (empty($accounts[$account_machine_name])) {
      $accounts[$account_machine_name] = [];
    }
    else {
      $account_machine_name = $values['account_machine_name'];
    }
    $accounts[$account_machine_name]['account_name'] = $values['account_name'];
    $accounts[$account_machine_name]['consumer_key'] = $values['consumer_key'];
    $accounts[$account_machine_name]['consumer_secret'] = $values['consumer_secret'];
    $accounts[$account_machine_name]['oauth_token'] = $values['oauth_token'];
    $accounts[$account_machine_name]['oauth_token_secret'] = $values['oauth_token_secret'];
    $this->config('tweet_feed.twitter_accounts')
      ->set('accounts', $accounts)
      ->save();
  }
}
