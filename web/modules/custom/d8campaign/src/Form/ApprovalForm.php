<?php

namespace Drupal\d8campaign\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\file\Entity\File;


class ApprovalForm extends FormBase{
    
    public function getFormId()    {
        return 'd8campaign_approval_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state){

        if($_GET['ac']){
            $entity = Node::load($_GET['ac']);
            $field_campaign_type = isset($entity->get('field_campaign_type')->getValue()[0]['target_id']) ? $entity->get('field_campaign_type')->getValue()[0]['target_id']: "";
            if($field_campaign_type){
                $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($field_campaign_type);
                $ac_declare = isset($term->get('field_account_declaration')->getValue()[0]['target_id']) ? $term->get('field_account_declaration')->getValue()[0]['target_id']: "";
                $declare_path = "#";
                if($ac_declare){
                    $file =File::load($ac_declare);
                    $declare_path = $file->url();
                }
                $in_declare = isset($term->get('field_insurance_declaration_pdf')->getValue()[0]['target_id']) ? $term->get('field_insurance_declaration_pdf')->getValue()[0]['target_id']: "";
                $in_path = "#";
                if($in_declare){
                    $file =File::load($in_declare);
                    $in_path = $file->url();
                }

                $field_landing_page_link = isset($term->get('field_landing_page_link')->getValue()[0]['value']) ? $term->get('field_landing_page_link')->getValue()[0]['value']: "";
                
                
            }

            $form['signature_declaration'] = array(
                '#type'=> 'checkbox',
                '#title'=> 'I/We have read and agree to the terms and conditions mentioned in Signature Account Declaration, <a href="'.$declare_path.'" target="_blank">Click here to view Declartion </a>',
                '#required'=> TRUE,
                '#prefix'=>'<h1>Signature account Declaration</h1>',
            );
            $form['insurance_declaration'] = array(
                '#type'=> 'checkbox',
                '#title'=> t('I/We have read and agree to the terms and conditions mentioned in Insurance Declaration,  <a href="'.$in_path.'" target="_blank">Click here to view Declartion </a>'),
                '#required'=> TRUE,
                '#prefix'=> '<h1>Insurance Declaration</h1>',
            );
            $form['nid'] = array(
                '#type' => 'hidden',
                '#value' => $_GET['ac'], 
            );

            $form['text']['#markup'] = "<a href='".$field_landing_page_link."?ac=".$_GET['ac']."' class='button button--primary cancel-btn'>Cancel</a>";
            $form['actions']['#type'] = 'actions';
            $form['actions']['submit'] = array(
                '#type' => 'submit',
                '#value' => $this->t('Submit'),
                '#button_type' => 'primary',
            );
            
        }else{
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $form;
    }
  
    public function validateForm(array &$form, FormStateInterface $form_state){}

    public function submitForm(array &$form, FormStateInterface $form_state){
        
        $nid =  $form_state->getValue('nid');
        $node = Node::load($nid);
        $node->field_insurance_declaration = 1;
        $node->field_signature_account_declarat = 1;
        $node->field_campaign_status = "accepted";
        $node->save();

        $field_email = isset($node->get('field_email')->getValue()[0]['value']) ? $node->get('field_email')->getValue()[0]['value']: "";
        $field_campaign_type = isset($node->get('field_campaign_type')->getValue()[0]['target_id']) ? $node->get('field_campaign_type')->getValue()[0]['target_id']: "";
        $field_phone = isset($node->get('field_phone')->getValue()[0]['value']) ? $node->get('field_phone')->getValue()[0]['value']: "";
        if(!empty($field_campaign_type) && !empty($field_email)){
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($field_campaign_type);
            $email_template = isset($term->get('field_submission_email')->getValue()[0]['value']) ? $term->get('field_submission_email')->getValue()[0]['value']: "";
            $email_subject = isset($term->get('field_submission_email_subject')->getValue()[0]['value']) ? $term->get('field_submission_email_subject')->getValue()[0]['value']: "";
          
            if(!empty($email_subject) && !empty($email_template)){
                $newMail = \Drupal::service('plugin.manager.mail');
                $params = array();

                $host = $_SERVER['SERVER_NAME'];
                $port = $_SERVER['SERVER_PORT'];
                if($port == 443){
                    $ref_url = "https://".$host;
                }else{
                    $ref_url = "http://".$host;
                }

                $params['template'] = $email_template;
                $params['subject'] = $email_subject;
                if($newMail->mail('d8campaign', 'SVCCampaign_signature', $field_email, 'en', $params, $reply = NULL, $send = TRUE)){
                    drupal_set_message("Email has been sent for further details");
                }

                
            }
        }

        if(is_numeric($field_phone) && strlen($field_phone) == 10 && $field_campaign_type){

            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($field_campaign_type);
            $sms_template = isset($term->get('field_submission_sms')->getValue()[0]['value']) ? $term->get('field_submission_sms')->getValue()[0]['value']: "";

            if(!empty($sms_template)){
                $host = $_SERVER['SERVER_NAME'];
                $port = $_SERVER['SERVER_PORT'];
                if($port == 443){
                    $ref_url = "https://".$host;
                }else{
                    $ref_url = "http://".$host;
                }

                $sms_template = urlencode( $sms_template );

                $field_phone = "+91".$field_phone;

                $config = \Drupal::service('config.factory')->getEditable('d8campaign.api');
                //$config = $this->config('d8campaign.api');
                $sms_username =  $config->get('sms_username');
                $sms_password =  $config->get('sms_password');
                $sms_senderid =  $config->get('sms_senderid');
                $sms_cdmaheader =  $config->get('sms_cdmaheader');           

                $curlSession = curl_init();
                curl_setopt($curlSession, CURLOPT_URL, 'https://hapi.smsapi.org/SendSMS.aspx?UserName='.$sms_username.'&password='.$sms_password.'&MobileNo='.$field_phone.'&SenderID='.$sms_senderid.'&CDMAHeader='.$sms_cdmaheader.'&Message='.$sms_template.'');
                curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
                curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

                $jsonData = curl_exec($curlSession);
                curl_close($curlSession);


                if (strpos($jsonData, 'MessageSent') !== false) {
                    drupal_set_message("SMS has been sent for further details");
                }
            }
        }




        //drupal_set_message("Thanks, your request is submitted");
        if($_GET['type']){
            $path = '/campaign-account-thanks?type='.$_GET['type'];
        }else{
            $path = '/campaign-account-thanks';
        }
        
        $response = new RedirectResponse($path);
        $response->send();
    }

    
}
