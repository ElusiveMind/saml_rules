<?php
namespace Drupal\saml_rules\Controller;

use Drupal\Core\Link;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class SAMLRulesUserFieldRulesMatrix.
 */
class SAMLRulesUserFieldRulesMatrix extends ControllerBase {
  /**
   * display_rules().
   *
   * @return array
   *   Return render array of a table of elements that make up the list
   *   of available authentication rules or an empty list. Designed to be
   *   handled by Drupal's configuration management system.
   */
  public function display_rules() {
    $config = $this->config('saml_rules.user_field_rules');

    $header = [
      ['data' => $this->t('Rule Name')],
      ['data' => $this->t('Rule Machine Name')],
      ['data' => $this->t('Edit')],
      ['data' => $this->t('Delete')],
    ];

    $rows = [];
    $rules = $config->get('rules');
    if (!empty($rules)) {
      foreach ($rules as $rule_machine_name => $rule) {
        $edit_link = Link::createFromRoute($this->t('Edit'), 'saml_rules.user_field_rules_edit', ['rule_machine_name' => $rule_machine_name]);
        $delete_link = Link::createFromRoute($this->t('Delete'), 'saml_rules.user_field_rules_delete', ['rule_machine_name' => $rule_machine_name]);
        $row = [
          ['data' => $rule['rule_name']],
          ['data' => $rule_machine_name],
          ['data' => $edit_link],
          ['data' => $delete_link],
        ];
        $rows[] = $row;
      }
    }
    return [
      '#type' => 'table',
      '#attributes' => ['class' => ['table table-striped']],
      '#prefix' => NULL,
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => 'THERE ARE NO USER FIELD RULES CURRENTLY CREATED.',
    ];
  }
}
