<?php

namespace Drupal\saml_rules\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SAMLRulesUserFieldRuleForm.
 */
class SAMLRulesUserFieldRuleForm extends ConfigFormBase {

 /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'saml_rules.user_field_rules',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'saml_rules_user_field_rules_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $rule_machine_name = NULL) {
    // Get all of the fields we can map to the user.
    $configurable_fields = ['name' => 'name', 'pass' => 'pass', 'mail' => 'mail'];
    $fields = array_keys(\Drupal::service('entity_field.manager')->getFieldDefinitions('user', 'user'));
    foreach ($fields as $field) {
      $parts = explode('_', $field);
      if ($parts[0] == 'field') {
        $configurable_fields[$field] = $field;
      }
    }

    // Get our list of SAML attributes that come through the SAML response.
    $saml_fields = [];
    $config = \Drupal::config('saml_rules.settings');
    $fields = $config->get('attributes');
    if (!empty($fields)) {
      foreach ($fields as $field) {
        $saml_fields[$field] = $field;
      }
    }

    // If there are no SAML keys, then we can't do anything. Likely because they have not
    // logged in via SAML provider yet.
    if (empty($saml_fields)) {
      drupal_set_message('Cannot configure User Field Rules because there are no available SAML attributes. That may be because you have not interfaced with the SAML service yet. Login using the SAML service and this should provide the SAML response attributes needed.', 'error');
      $response = new RedirectResponse(\Drupal::url('saml_rules.user_field_rules_view'));
      $response->send();
    }
    
    // Set up our settings form for this particular account (new or update)
    $config = $this->config('saml_rules.user_field_rules');
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
      $rule_type = $rule['rule_type'];
      $condition_saml = $rule['condition_saml'];
      $condition_operand = $rule['condition_operand'];
      $condition_value = $rule['condition_value'];
      $user_field = $rule['user_field'];
      $user_value = $rule['user_value'];
    }
    else {
      $rule_name = $rule_type = $condition_saml = NULL;
      $condition_operand = $condition_value = $user_field = NULL;
      $user_value = NULL;
    }

    $form['user_field_rules'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('SAML Rules: User Field Rule'),
      '#weight' => 0,
    );
    $form['user_field_rules']['rule_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name for this rule.'),
      '#description' => $this->t('A unique name for this rule for descriptive purposes.'),
      '#max_length' => 255,
      '#required' => TRUE,
      '#default_value' => $rule_name,
      '#weight' => 10,
    );
    $form['user_field_rules']['rule_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Rule Type'),
      '#options' => [
        'assignment' => 'Value Assignment',
        'condition' => 'Conditional Assignment',
      ],
      '#required' => TRUE,
      '#default_value' => $rule_type,
      '#attributes' => [
        'id' => 'field_rule_type_action',
      ],
      '#weight' => 20,
    );
    $form['user_field_rules']['condition_saml'] = array(
      '#type' => 'select',
      '#title' => $this->t('SAML field to check value'),
      '#options' => $saml_fields,
      '#default_value' => $condition_saml,
      '#states' => [
        'visible' => [
          ':input[id="field_rule_type_action"]' => ['value' => 'condition'],
        ],
        'required' => [
          ':input[id="field_rule_type_action"]' => ['value' => 'condition'],
        ],
      ],
      '#weight' => 30,
    );
    $form['user_field_rules']['condition_operand'] = [
      '#type' => 'select',
      '#title' => $this->t('Condition Operand'),
      '#options' => [
        'equal' => $this->t('Equal'),
        'not_equal' => $this->t('Not Equal'),
      ],
      '#default_value' => $condition_operand,
      '#states' => [
        'visible' => [
          ':input[id="field_rule_type_action"]' => ['value' => 'condition'],
        ],
        'required' => [
          ':input[id="field_rule_type_action"]' => ['value' => 'condition'],
        ],
      ],
      '#weight' => 40,
    ];
    $form['user_field_rules']['condition_value'] = array(
      '#type' => 'textfield',
      '#title' => t('Condition Value'),
      '#description' => $this->t('The value to be compared to the SAML field in this condition'),
      '#default_value' => $condition_value,
      '#states' => [
        'visible' => [
          ':input[id="field_rule_type_action"]' => ['value' => 'condition'],
        ],
        'required' => [
          ':input[id="field_rule_type_action"]' => ['value' => 'condition'],
        ],
      ],
      '#weight' => 50,
    );
    $form['user_field_rules']['user_field'] = array(
      '#type' => 'select',
      '#title' => $this->t('User field assignment'),
      '#description' => $this->t('The field in the user profile the value will be assigned.'),
      '#options' => $configurable_fields,
      '#default_value' => $user_field,
      '#required' => TRUE,
      '#weight' => 60,
    );
    $form['user_field_rules']['user_value'] = array(
      '#type' => 'textfield',
      '#title' => t('User field value'),
      '#description' => $this->t('The value to be be assigned to the user field. Can be tokens for SAML variables by encasing in brackets ([]). Available SAML attributes: ' . join(', ', array_keys($saml_fields))),
      '#default_value' => $user_value,
      '#required' => TRUE,
      '#weight' => 70,
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
    $config = $this->config('saml_rules.user_field_rules');
    $rules = $config->get('rules');
    $rule_machine_name = preg_replace('/[^a-z0-9]+/', '_', strtolower($values['rule_name']));

    // Check for rule machine name collision.
    if (empty($values['rule_update']) && !empty($rule[$rule_machine_name])) {
      $suffix = 1;
      do {
        $new_rule_machine_name = $rule_machine_name . '_' . $suffix;
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
    $rules[$rule_machine_name]['rule_type'] = $values['rule_type'];
    $rules[$rule_machine_name]['condition_saml'] = $values['condition_saml'];
    $rules[$rule_machine_name]['condition_value'] = $values['condition_value'];
    $rules[$rule_machine_name]['condition_operand'] = $values['condition_operand'];
    $rules[$rule_machine_name]['user_field'] = $values['user_field'];
    $rules[$rule_machine_name]['user_value'] = $values['user_value'];

    $this->config('saml_rules.user_field_rules')
      ->set('rules', $rules)
      ->save();
    
    $form_state->setRedirect('saml_rules.user_field_rules_view');
  }
}
