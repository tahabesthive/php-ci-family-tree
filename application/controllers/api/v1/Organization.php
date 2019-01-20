<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

/**
 * This is an @package class which help to create api calls for @subpackage 
 * 
 * @package         PipeDrive Test
 * @subpackage      Organization
 * @category        Controller
 * @author          Taha Ali Adil
 */
class Organization extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key

        $this->load->model('organization_model');
        $this->load->library('form_validation');

    }

    public function search_post(){

        // decode the payload to POST data.
        $payload = json_decode(trim(file_get_contents('php://input')), true);
        $error = false;

        // validate the json payload
        if(!$payload){
            $error = lang('text_rest_request_json');;
        }else{
            $_POST = $payload;
            $this->form_validation->set_rules('term', 'term', 'trim|required|min_length[3]');
            $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|less_than[101]');
            $this->form_validation->set_rules('start', 'start', 'trim|numeric');
            $this->form_validation->set_rules('sort', 'sort', 'trim|in_list[asc,desc]');

            if($this->form_validation->run() == FALSE) { 
            $error = validation_errors(); 
            }
        }

        //handle the payload errors
        if($error){
            $this->response([
                'status' => FALSE,
                'message' => $error
            ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        //Search the data in database for term
        $result = $this->organization_model->search($this->input->post('term',TRUE), $this->input->post('start'), $this->input->post('limit'), $this->input->post('sort'));

        $this->set_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

    }

    public function add_post()
    {

        // decode the payload to $_POST data.
        $payload = json_decode(trim(file_get_contents('php://input')), true);
        $error = false;

        // validate the json payload
        if(!$payload){
            $error = lang('text_rest_request_json');;
        }else{
            $_POST = $payload;
            $this->form_validation->set_rules('org_name', 'org_name', 'trim|required|min_length[3]');

            if(!is_array($this->input->post('daughters'))){
                $error = lang('text_rest_pattern_custom_message'); 
            }
        
            if($this->form_validation->run() == FALSE) { 
            $error = validation_errors(); 
            }
        } 

        //handle the payload errors
        if($error){
            $this->response([
                'status' => FALSE,
                'message' => $error
            ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // run the loop extract all organization name and search in database if doesn't exist create with bulk
        $list = $this->organization_model->import_organizations();

        if(!$list){
            $error = lang('text_rest_pattern_custom_message');   
        }
        
        //handle the extract all organization name
        if($error){
            $this->response([
                'status' => FALSE,
                'message' => $error
            ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        $response = $this->organization_model->organizaton_relations($list);

        if(!$response){
            $error = lang('text_rest_pattern_custom_message_relation');  
        }

        //handle the payload errors
        if($error){
            $this->response([
                'status' => FALSE,
                'message' => $error
            ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        $message = [
            'message' => lang('text_rest_respone_message')
        ];

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code

    }

}
