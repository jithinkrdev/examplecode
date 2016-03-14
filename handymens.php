<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Handymens extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	 
	 private $table = 'handymens'; 
	public function __construct()
   {
		parent::__construct();
		
		
		// Your own constructor code
		$this->load->model(array('handymens_model','services_model'));
		
		
   } 
	 
	 
	 
	public function index()
	{
		$uri_array = $this->uri->uri_to_assoc();
		$page = '';
		if(isset($uri_array['page']))
		{
			$page = $uri_array['page'];
			unset($uri_array['page']);
		}
		
		$data['results'] =	$this->handymens_model->select_all();
		$total = $this->db->count_all_results();
		$config['base_url'] = site_url($this->uri->assoc_to_uri($uri_array).'/page/');
		$config['total_rows'] = $total;
		$config['per_page'] = 20; 

		$this->pagination->initialize($config); 

		$data['page_links'] = $this->pagination->create_links();
		$this->load->view('admin/handymens/index',$data);	
		
	}
	
	
	
	
	public function view($id='')
	{
		
		$result =	$this->handymens_model->select_by_field('id',$id);
		
		
		if($result)
		{
			
			$data['skills'] =	$this->handymens_model->get_skills($id);
			
			$q =	$this->attachments_model->select_by_field('handymens','parent',$id);			
			$attachments = $q->result_array();
			
			$data['result'] = $result;			
	
			$data['attachments'] = $attachments;
			
			
		}
		else
		{
					redirect('error');
		}
		
		
	

		$this->load->view('admin/handymens/view',$data);

	}
	
	public function add()
	{
		
		$config = array(
               array('field'   => 'title','label'   => 'Title','rules'   => 'required'),           
			   array('field'   => 'coverage','label'   => 'Coverage','rules'   => 'required|max_length[2]|alpha'),  
			   array('field'   => 'email','label'   => 'Email','rules'   => 'required|is_unique[handymens.email]|valid_email'),
			   array('field'   => 'content','label'   => 'Content','rules'   => 'required'),
			   
            );

		$this->form_validation->set_rules($config);
		$this->form_validation->set_error_delimiters('<p class="help-block" ><small class="form-error" style="color:red;">', '</small></p>');
		
		if ($this->form_validation->run() == FALSE)
		{
				$data['services'] = $this->services_model->services();
				
				$this->load->view('admin/handymens/add',$data);
				
		}
		else
		{
			
			$id = $this->handymens_model->save();
			if($id)
			{
				$this->attachments_model->save('handymens',$id,$this->input->post('attachments'));
				$this->handymens_model->add_skills($id);
				
			}
			$this->session->set_flashdata('message', array('content' => 'New Handymen Added Successfully', 'type' => 'alert_success' ));
			redirect('admin/handymens');
		}
		
	
	}
	
	public function edit($id='')
	{
		
		
		$config = array(
               array('field'   => 'title','label'   => 'Title','rules'   => 'required'),           
			   array('field'   => 'coverage','label'   => 'Coverage','rules'   => 'required|max_length[2]|alpha'),  
			   
			   array('field'   => 'content','label'   => 'Content','rules'   => 'required'),
			   
            );
		$result =	$this->handymens_model->select_by_field('id',$id);
		if(isset($result['email']) && $this->input->post('email'))
		{
			if($result['email'] != $this->input->post('email'))
			{
				$config[] =  array('field'   => 'email','label'   => 'Email','rules'   => 'required|is_unique[handymens.email]|valid_email');
			}
			else
			{
				$config[] = array('field'   => 'email','label'   => 'Email','rules'   => 'required|valid_email');
			}
		}
		$this->form_validation->set_rules($config);
		$this->form_validation->set_error_delimiters('<p class="help-block" ><small class="form-error" style="color:red;">', '</small></p>');
		
		if ($this->form_validation->run() == FALSE)
		{
				
				
				
				
				
				if($result)
				{
					
					$q =	$this->attachments_model->select_by_field('handymens','parent',$id);			
					$attachments = $q->result_array();
					$data['attachments'] = $attachments;
					$data['result'] = $result;	
					
					$data['services'] = $this->services_model->services();
		
				
					$this->load->view('admin/handymens/edit',$data);
					
				}
				else
				{
					redirect('error');
				}
				
				
		}
		else
		{
			$this->handymens_model->update($id);
			
			$this->attachments_model->save('handymens',$id,$this->input->post('attachments'));
			
			$this->handymens_model->add_skills($id);
			
			$this->session->set_flashdata('message',array('content' => 'Handymen Updated Successfully', 'type' => 'alert_success' ));
			redirect('admin/handymens');
		}
		
		
		
	
	
	}
	
	
	public function update_status()
	{
		
		if(!$id  = $this->input->post('id')){ redirect('error'); }
		
		if($this->handymens_model->update_status($id))
		{
			
			$response = array('text'=>'Status updated successfully');
			echo json_encode($response);
			die();
			
		}
		else
		{
			$response = array('text'=>'Something went wrong. Please try again');
			echo json_encode($response);
			die();
		}
		//$this->session->set_flashdata('success','Task removed from list successfully');
		//redirect('tasks');
	}
	
	
	public function trash($id='')
	{
		if(!$id ){ redirect('error'); }
		$this->handymens_model->trash($id);
		$this->session->set_flashdata('message',array('content' => 'Project removed from list successfully', 'type' => 'alert_success' ));
		redirect('admin/handymens');
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */