<?php

namespace Drupal\d8campaign\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\ContentEntityInterface;
/**
 * Action description.
 *
 * @Action(
 *   id = "send_campaign_email",
 *   label = @Translation("Send Campaign Email"),
 *   type = ""
 * )
 */

class SendCampaignEmail extends ViewsBulkOperationsActionBase {

    use StringTranslationTrait;

    /**
     * {@inheritdoc}
     */
    public function execute(ContentEntityInterface $entity = NULL) {
        
        $nid = isset($entity->get('nid')->getValue()[0]['value']) ? $entity->get('nid')->getValue()[0]['value']: "";
        $title = isset($entity->get('title')->getValue()[0]['value']) ? $entity->get('title')->getValue()[0]['value']: "";
        $field_email = isset($entity->get('field_email')->getValue()[0]['value']) ? $entity->get('field_email')->getValue()[0]['value']: "";

        $field_campaign_type = isset($entity->get('field_campaign_type')->getValue()[0]['target_id']) ? $entity->get('field_campaign_type')->getValue()[0]['target_id']: "";
        if($field_campaign_type){
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($field_campaign_type);
            $email_template = isset($term->get('field_email_template')->getValue()[0]['value']) ? $term->get('field_email_template')->getValue()[0]['value']: "";
            $sms_template = isset($term->get('field_sms_template')->getValue()[0]['value']) ? $term->get('field_sms_template')->getValue()[0]['value']: "";
            $landing_page_link = isset($term->get('field_landing_page_link')->getValue()[0]['value']) ? $term->get('field_landing_page_link')->getValue()[0]['value']: "";
            $campaign_name = isset($term->get('name')->getValue()[0]['value']) ? $term->get('name')->getValue()[0]['value']: "";
            $email_subject = isset($term->get('field_email_subject')->getValue()[0]['value']) ? $term->get('field_email_subject')->getValue()[0]['value']: "";
            $status = isset($term->get('status')->getValue()[0]['value']) ? $term->get('status')->getValue()[0]['value']: "";
            \Drupal::logger('module_name')->notice('<pre><code>' . print_r($status, TRUE) . '</code></pre>' );
          
            if($status){
                $newMail = \Drupal::service('plugin.manager.mail');
                $params = array();

                $host = $_SERVER['SERVER_NAME'];
                $port = $_SERVER['SERVER_PORT'];
                if($port == 443){
                    $ref_url = "https://".$host;
                }else{
                    $ref_url = "http://".$host;
                }

                $landing_page_link = $ref_url."".$landing_page_link."?ac=".$nid;

                $email_template = str_replace("#username#",$title,$email_template);
                $email_template = str_replace("#landingpageurl#",$landing_page_link,$email_template);

                $params['template'] = $email_template;
                $params['subject'] = $email_subject;
                $newMail->mail('d8campaign', 'SVCCampaign_signature', $field_email, 'en', $params, $reply = NULL, $send = TRUE);

                if($nid){
                    $node = Node::load($nid);
                    $node->field_email_status = 1;
                    $node->save();
                }
            }else{
                return $this->t('This Campaingn is unpublished');
            }

            
        }        
        return $this->t('Email Send sucessfully');
    }

    /**
     * {@inheritdoc}
     */
    public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
       
        if ($object instanceof Node && $object->get('type')->getValue()[0]['target_id'] == "campaign") {
            return TRUE;
        }
        
        
        return FALSE;
    }
}