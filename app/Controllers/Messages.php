<?php

namespace App\Controllers;

require_once("Secure_Controller.php");

class Messages extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('messages');
		
		$this->sms_lib = new Sms_lib();
	}
	
	public function index()
	{
		echo view('messages/sms');
	}

	public function view($person_id = -1)
	{ 
		$info = $this->Person->get_info($person_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}
		$data['person_info'] = $info;

		echo view('messages/form_sms', $data);
	}

	public function send()
	{	
		$phone   = $this->input->post('phone');
		$message = $this->input->post('message');

		$response = $this->sms_lib->sendSMS($phone, $message);

		if($response)
		{
			echo json_encode(array('success' => TRUE, 'message' => lang('Messages.successfully_sent') . ' ' . $phone));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => lang('Messages.unsuccessfully_sent') . ' ' . $phone));
		}
	}
	
	public function send_form($person_id = -1)
	{	
		$phone   = $this->input->post('phone');
		$message = $this->input->post('message');

		$response = $this->sms_lib->sendSMS($phone, $message);

		if($response)
		{
			echo json_encode(array('success' => TRUE, 'message' => lang('Messages.successfully_sent') . ' ' . $phone, 'person_id' => $this->xss_clean($person_id)));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => lang('Messages.unsuccessfully_sent') . ' ' . $phone, 'person_id' => -1));
		}
	}
}
?>