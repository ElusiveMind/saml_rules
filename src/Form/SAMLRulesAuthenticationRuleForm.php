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
  public function buildForm(array $form, FormStateInterface $form_state, $rule_machine_name = NULL) {
    $config = $this->config('saml_rules.authentication_rules');
    // Set up our settings form for this particular account (new or update)
    if (!empty($rule_machine_name)) {
      $rules = $config->get('rules');
      $rule = $rules[$rule_machine_name];
      $form['rule_machine_name'] = [
        '#type' => 'hidden',
        '#value' => $rule_machine_name,
      ];
      $form['rule_update'] = [
        '#type' => 'hidden',
        '#value' => 1,
      ];

      $rule_name = $rule['rule_name'];
      $saml_attribute = $rule['saml_attribute'];
      $saml_value = $rule['saml_value'];

      // Actions: assign roles, update email, 
      $actions = $rule['actions'];
      $roles = $rule['roles'];
      $email = $rule['email'];
    }
    else {
      $rule_name = NULL;
      $saml_attribute = $saml_value = $actions = $email = NULL;
      $roles = ['authenticated' => 'authenticated'];
    }

    $form['authentication_rules'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('SAML Rules: Authentication Rule'),
    );
    $form['authentication_rules']['rule_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name for this rule.'),
      '#description' => $this->t('A unique name for this rule for descriptive purposes.'),
      '#max_length' => 255,
      '#required' => TRUE,
      '#default_value' => $rule_name,
    );
    $form['authentication_rules']['saml_attribute'] = array(
      '#type' => 'textfield',
      '#title' => t('Incoming SAML attribute to evaluate.'),
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
    $form['authentication_rules']['actions'] = [
      '#type' => 'radios',
      '#title' => $this->t('Type of action to take'),
      '#options' => [
        'roles' => $this->t('Assign roles to account'),
        'email' => $this->t('Alter email address depending on incoming value.'),
      ],
      '#required' => TRUE,
      '#default_value' => $actions,
      '#attributes' => [
        'id' => 'field_select_action',
      ],
    ];
    $form['authentication_rules']['roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Roles to be assigned.'),
      '#description' => $this->t('The roles to be assigned based on the criteria above. The "authenticated user" role should always be assigned.'),
      '#options' => array_map(['\Drupal\Component\Utility\Html', 'escape'], user_role_names(TRUE)),
      '#default_value' => $roles,
      '#states' => [
        'visible' => [
          ':input[id="field_select_action"]' => ['value' => 'roles'],
        ],
        'required' => [
          ':input[id="field_select_action"]' => ['value' => 'roles'],
        ],
      ],
    );
    $form['authentication_rules']['email'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Change email address value.'),
      '#description' => $this->t('Alter the email adress. Use SAML attribute values by placing the attribute name in brackets ([]).'),
      '#max_length' => 255,
      '#default_value' => $email,
      '#states' => [
        'visible' => [
          ':input[id="field_select_action"]' => ['value' => 'email'],
        ],
        'required' => [
          ':input[id="field_select_action"]' => ['value' => 'email'],
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
    $config = $this->config('saml_rules.authentication_rules');
    $rules = $config->get('rules');
    $rule_machine_name = preg_replace('/[^a-z0-9]+/', '_', strtolower($values['rule_name']));

    // Check for rule machine name collision.
    if (empty($values['rule_update']) && !empty($rule[$rule_machine_name])) {
      $suffix = 1;
      do {
        $new_rule_maching_name = $rule_machine_name . '_' . $suffix;
        $suffix++;
      }
      while (!empty($rules[$new_rule_machine_name]));
      $rule_machine_name = $new_rule_machine_name;
    }
    
    // Initialize or populate our rules array and machine name.
    if (empty($rules[$rule_machine_name])) {
      $rules[$rule_machine_name] = [];
    }
    else {
      $rule_machine_name = $values['rule_machine_name'];
    }

    // Assignments.
    $rules[$rule_machine_name]['rule_name'] = $values['rule_name'];
    $rules[$rule_machine_name]['saml_attribute'] = $values['saml_attribute'];
    $rules[$rule_machine_name]['saml_value'] = $values['saml_value'];
    $rules[$rule_machine_name]['actions'] = $values['actions'];

    // Calculate out all the roles being used. Do not store the roles not being used.
    $roles = [];
    foreach ($values['roles'] as $rk => $rv) {
      if ($rk == $rv) {
        $roles[] = $rv;
      }
    }
    $rules[$rule_machine_name]['roles'] = $roles;

    // Put our email field in as it was provided. Will be up to the hook to parse it.
    $rules[$rule_machine_name]['email'] = $values['email'];

    $this->config('saml_rules.authentication_rules')
      ->set('rules', $rules)
      ->save();
    
    $form_state->setRedirect('saml_rules.authentication_rules_view');

  }
}
