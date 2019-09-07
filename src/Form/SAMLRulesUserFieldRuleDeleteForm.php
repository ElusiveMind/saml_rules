<?php

namespace Drupal\saml_rules\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Confirmation of SAML User Field Rule deletion.
 *
 * @ingroup saml_rules
 */
class SAMLRulesUserFieldRuleDeleteForm extends ConfirmFormBase {

  /**
   * ID of the item to delete.
   *
   * @var string
   */
  protected $rule_machine_name;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $rule_machine_name = NULL) {
    $this->rule_machine_name = $rule_machine_name;
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('saml_rules.user_field_rules');
    $rules = $config->get('rules');
    unset($rules[$this->rule_machine_name]);
    $config->set('rules', $rule)->save();
    drupal_set_message($this->t('The user field rule %rule_name was deleted.', [
      '%rule_name' => $this->rule_machine_name,
    ]));
    $form_state->setRedirect('saml_rules.user_field_rules_view');
    return;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "saml_rules_user_field_rule_delete_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('saml_rules.user_field_rules_view');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete %rule_name?', ['%rule_name' => $this->rule_machine_name]);
  }

}
