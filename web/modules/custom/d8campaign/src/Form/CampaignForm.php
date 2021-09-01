<?php

namespace Drupal\d8campaign\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

use Drupal\Core\Batch\BatchBuilder;

class CampaignForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'd8campaign_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $vid = 'campaign_type';
        $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
        foreach ($terms as $term) {
            $term_data[$term->tid] = $term->name;
        }
        //print "<pre>";print_r($file->get('uri')->getValue()[0]['value']);exit;
        //print "<pre>";print_r($term_data);exit;
        

        $types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();


        if (array_key_exists("campaign", $types) & !empty($term_data)) {

            $form['campaign_type'] = array(
                '#type' => 'select',
                '#title' => $this->t('Campaign type'),
                '#options' => $term_data,
                '#required' => true
            );

            $form['upload_csv'] = array(
                '#type' => 'managed_file',
                '#title' => $this->t('Upload CSV'),
                '#upload_location' => 'public://campaign-csv',
                '#upload_validators' => array(
                    'file_validate_extensions' => ['csv'],
                ),
                '#required' => true
            );

            

            $form['actions']['#type'] = 'actions';
            $form['actions']['submit'] = array(
                '#type' => 'submit',
                '#value' => $this->t('Upload'),
                '#button_type' => 'primary',
            );
        } else {
            drupal_set_message("Please create 'campagin' content types and fields to proceed OR Create Terms in Taxonomy campaign_type", "error");
        }


        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        //drupal_set_message("Code is under progress.","error");
        
        $form_file = $form_state->getValue('upload_csv', 0);
        $campaign_type = $form_state->getValue('campaign_type');

        if (isset($form_file[0]) && !empty($form_file[0])) {
            $file = File::load($form_file[0]);
            $file->setPermanent();
            $checksave = $file->save();
            $created_nodes = array();

            if ($checksave == 2) {
                drupal_set_message("File upload successfully");
                $saved_file = File::load($form_file[0]);
                $destination = $saved_file->get('uri')->getValue()[0]['value'];
                $file_parse = fopen($destination, "r");
                $i = 0;
                while (!feof($file_parse)) {
                    $customer = fgetcsv($file_parse);
                    $customer[5] = $campaign_type;
                    $created_nodes[] = $customer;
                    
                }
                fclose($file_parse);


                
                $batch_builder = (new BatchBuilder())->setTitle($this->t('Creating Campaign customers'))->setProgressMessage('')->addOperation([get_class($this), 'processBatch'], [$created_nodes]);
                batch_set($batch_builder->toArray());
                //print "<pre>";print_r($campaign_type);
                //print "<pre>";print_r($created_nodes);exit;
            }
        }
    }

    public static function processBatch($nids, array &$context)
    {
        $number = count($nids);

        $message = \Drupal::translation()->formatPlural($number, 'One customer created', '@count customers created');
        drupal_set_message($message);

        // Initiate multistep processing.
        if (empty($context['sandbox'])) {
            $context['sandbox']['progress'] = 0;
            $context['sandbox']['max'] = $number;
        }

        // Process the next 100 if there are at least 100 left. Otherwise,
        // we process the remaining number.
        $batch_size = 100;
        $max = $context['sandbox']['progress'] + $batch_size;
        if ($max > $context['sandbox']['max']) {
            $max = $context['sandbox']['max'];
        }

        // Start where we left off last time.
        $start = $context['sandbox']['progress'];
        for ($i = $start; $i < $max; $i++) {
            $data = $nids[$i];

            $node = Node::create(array(
                'type' => 'campaign',
                'title' => $data[0],
                'langcode' => 'en',
                'uid' => '1',
                'status' => 1,
                'field_account_number' => $data[1],
                'field_account_type' => $data[2],
                'field_email' => $data[3],
                'field_phone' => $data[4],
                'field_campaign_type' => $data[5],
            ));

            $node->save();

            // Update our progress!
            $context['sandbox']['progress']++;
        }

        // Multistep processing : report progress.
        if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
            $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
        }
    }
}
