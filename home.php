<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Home extends MY_Controller {

	public function __construct()
    {
		
       parent::__construct();
     
    }

	public function index()
	{
		//$this->load->view('welcome_message');
		$data = array();
		//echo "<pre>"; print_r($this->input->post('submit'));
		//pre($_POST);
		
		
		//exit();
		if($this->input->post('submit')=="Login"){
			$this->form_validation->set_rules('username', 'Username', 'required');
			$this->form_validation->set_rules('password', 'Password', 'required');
			if ($this->form_validation->run() == TRUE){
				$pass = md5($this->input->post ('password'));
				//echo $pass;
				$query = $this->db->get_where('admin',array('username'=>$this->input->post('username'),
                                         'password'=>$pass));
				//pre($query); exit;
				if($query->num_rows()==1){
					//pre($query->row_array());exit;
					$this->session->set_userdata($query->row_array());
					//$this->session->set_userdata('admin','stored');
					$this->session->set_flashdata( 'message', array('content' => 'Logged in successfully', 'type' => 'success' ));
					//pre($this->session->all_userdata());exit;
					//pre($_SESSION);exit;
					//pre($this->session->userdata('username'));exit;
					redirect("admin/home/dashboard");
				}
				else{
					$data['err'] = "Authentication failed.";
					
				}
			}
		}
		$this->load->view('admin/login',$data);
	}
	
	public function dashboard()
	{	
		//$data['item'] = $this->session->all_userdata();
		$data['item'] = $_SESSION;
		//pre($data['item']);exit;
		if(!empty($data['item']['admin']['username'])){
			$this->load->view('admin/home',$data);
		}else if(!empty($data['item']['username'])){
			$this->load->view('admin/home',$data);
		}else{
			$this->session->set_flashdata( 'message', array( 'content' => 'Please login to proceed', 'type' => 'success' ));
			redirect("admin/home/index");
		}	
	}
	public function logout()
	{	
			//$this->load->view('admin/login',$data);
			session_destroy();
			$this->session->unset_userdata('admin');
			$this->session->set_flashdata( 'message', array( 'content' => 'Logged out successfully', 'type' => 'success' ));
			redirect("admin/home/index");
		
	}

	
}
?>