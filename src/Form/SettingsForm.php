<?php

namespace Drupal\cs_notify\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure CS Notify settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'cs_notify.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'cs_notify_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $config = $this->config('cs_notify.settings');

    $form['environment'] = [
      '#type' => 'radios',
      '#title' => $this->t('Environment'),
      '#default_value' => $config->get('environment') ?? 'test',
      '#options' => [
        'test' => $this->t('Test  <br/><small>(uses https://test.notify.digital.gov.bc.ca/)</small>'),
        'prod' => $this->t('Production <br/><small>(uses https://notify.digital.gov.bc.ca/)</small>'),
      ],
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('You need a different key for test vs production environments. Get the appropriate one from:<ul> 
          <li>https://test.notify.digital.gov.bc.ca/settings, or </li>
          <li>https://notify.digital.gov.bc.ca/settings</li>
          </ul>'),
      '#default_value' => $config->get('api_key'),
    ];

    $form['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Request timeout'),
      '#description' => $this->t('HTTP timeout in seconds.'),
      '#default_value' => $config->get('timeout') ?? 30,
      '#min' => 1,
      '#required' => TRUE,
    ];

    $form['process_notify_queues_during_cron'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Process Notify queues using Drupal cron'),
      '#description' => $this->t("<ul>
        <li>Leave this enabled on a 'normal' Drupal site, to have Notify queues  processed by Drupal's <em>cron</em>. 
        The more frequently cron runs, the more quickly queued emails will be sent.</li>
        
        <li>If you run '<code>drush queue:run</code>' in your own process (such as we do on Openshift in BC Gov's 'Basebuild'),
        then disable this setting to avoid duplicate processing of the Notify queues.</li>
      "),
      '#default_value' => $config->get('process_notify_queues_during_cron') ?? TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $this->configFactory
      ->getEditable('cs_notify.settings')
      ->set('environment',   $form_state->getValue('environment'))
      ->set('api_key',       $form_state->getValue('api_key'))
      ->set('timeout', (int) $form_state->getValue('timeout'))
      ->set('process_notify_queues_during_cron', 
                      (bool) $form_state->getValue('process_notify_queues_during_cron'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}