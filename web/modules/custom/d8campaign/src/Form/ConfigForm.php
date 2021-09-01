<?php

namespace Drupal\d8campaign\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


class ConfigForm extends ConfigFormBase{

    protected function getEditableConfigNames() {
        return [
          'd8campaign.api',
        ];
    }
    
    public function getFormId()    {
        return 'admin_campaign_config_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state){

        //$config = \Drupal::service('config.factory')->getEditable('d8campaign.api');
        $config = $this->config('d8campaign.api');
        $sms_username =  $config->get('sms_username');
        $sms_password =  $config->get('sms_password');
        $sms_senderid =  $config->get('sms_senderid');
        $sms_cdmaheader =  $config->get('sms_cdmaheader');
        $sms_otp_template =  $config->get('sms_otp_template');
       
        $form['sms_username'] = array(
            '#type' => 'textfield',
            '#title' => t('SMS Username'),
            '#required' => TRUE,
            '#default_value' => $sms_username
        );
        $form['sms_password'] = array(
            '#type' => 'textfield',
            '#title' => t('SMS Password'),
            '#required' => TRUE,
            '#default_value' => $sms_password
        );
        $form['sms_senderid'] = array(
            '#type' => 'textfield',
            '#title' => t('SMS Sender ID'),
            '#required' => TRUE,
            '#default_value' => $sms_senderid
        );
        $form['sms_cdmaheader'] = array(
            '#type' => 'textfield',
            '#title' => t('SMS CDMA Header'),
            '#required' => TRUE,
            '#default_value' => $sms_cdmaheader
        );
        $form['sms_otp_template'] = array(
            '#type' => 'textarea',
            '#title' => t('SMS OTP Template'),
            '#required' => TRUE,
            '#default_value' => $sms_otp_template
        );
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#button_type' => 'primary',
        );
        return $form;
    }
  
    public function validateForm(array &$form, FormStateInterface $form_state){}

    public function submitForm(array &$form, FormStateInterface $form_state){

        parent::submitForm($form, $form_state);
        
        $sms_username = $form_state->getValue('sms_username');
        $sms_password = $form_state->getValue('sms_password');
        $sms_senderid = $form_state->getValue('sms_senderid');
        $sms_cdmaheader = $form_state->getValue('sms_cdmaheader');
        $sms_otp_template = $form_state->getValue('sms_otp_template');
        

        //$config = \Drupal::service('config.factory')->getEditable('d8campaign.api');
        $config = $this->config('d8campaign.api');
        $config->set('sms_username', $sms_username);
        $config->set('sms_password', $sms_password);
        $config->set('sms_senderid', $sms_senderid);
        $config->set('sms_cdmaheader', $sms_cdmaheader);
        $config->set('sms_otp_template', $sms_otp_template);
        
        $config->save();

       /* if($config->save()){
            drupal_set_message("updated");
        } */ 
        
    }

    
}
