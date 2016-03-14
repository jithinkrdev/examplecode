<?php 

class services_model extends CI_Model {
	public $table = 'services';
	
    function __construct()
    {
        parent::__construct();
		
    }
	
	
	
	function select_all()
	{	
		if($this->uri->segment(1)!='admin')
		{
			$this->db->where('status',1);
		}
		$query = $this->db->get($this->table);
	
		return $query->result_array();
	}
	
	function select_by_field($field='',$val='')
	{
		
		
		$this->db->where($field,$val);
	
		if($this->uri->segment(1)!='admin')
		{
			$this->db->where('status',1);
		}
		$query = $this->db->get($this->table);
		
		if($query->num_rows > 1)
		{
			return $query->result_array();
		}
		else
		{
			return $query->row_array();
		}
		
	}
	
	public function services()
	{
		$services = $this->services_model->select_all();
		$new_services = array();
		foreach($services as $row)
		{
			
			if($row['parent'])
			{
				$new_services[$row['parent']]['child'][] = elements(array('id','title','work_type'),$row);
			}
			else
			{
				$new_services[$row['id']] = elements(array('id','title'),$row);
			}
			
		} 
		return $new_services;
	}
	
	public function save()
	{
		$post = array();
		$fields = array('title','sub_title','content','order','parent','work_type','member_price','nonmember_price');
		foreach(elements($fields,$this->input->post()) as $key => $val)
		{
			$post[$key] = $this->input->post($key);
		}
		
		$post['created_date'] = date('Y-m-d H:i:s');
		
			
		$post['status'] = 1;	
		$flag = $this->db->insert($this->table,$post);
		if($flag)
		{
			return $this->db->insert_id();
		}
		else
		{
			return false;
		}
	}

	
	public function update($id)
	{
		if(!$id){ redirect('error'); }
		$post = array();
		$fields = array('title','sub_title','content','order','parent','work_type','member_price','nonmember_price');
		foreach(elements($fields,$this->input->post()) as $key => $val)
		{
			$post[$key] = $this->input->post($key);
		}
			
		$post['updated_date'] = date('Y-m-d H:i:s');
		//$post['added_by'] = element('id',$this->load->get_var('userdata'));			
		$this->db->where('id',$id);
		return $this->db->update($this->table,$post);
		
	}
	
	
	
	public function trash($id='')
	{
		$q =	$this->attachments_model->select_by_field('services','parent',$id);	
		$result = $q->result_array();
		foreach($result as $row)
		{
			$this->attachments_model->delete($row['id']);
		}
		
		
		
		
		$this->db->where('id',$id);
		
		return $this->db->delete($this->table);
		
	}
	
	function display($field='',$val='',$display='title')
	{
		$this->db->where($field,$val);
		$result = $this->db->get($this->table)->row_array();
		return ($result)?$result[$display]:false;
	}
	
	function list_all_services_by_user($id)
	{
		$this->db->select('*');
		$this->db->from('accomodation_listing');
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$result[] = $this->db->get()->result();		
		
		$this->db->select('*');
		$this->db->from('activities_sports');
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$result[] = $this->db->get()->result();		
		
		$this->db->select('*');
		$this->db->from('other_listing');
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$result[] = $this->db->get()->result();		
		
		$this->db->select('*');
		$this->db->from('rental_charters');
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$result[] = $this->db->get()->result();		
		
		$this->db->select('*');
		$this->db->from('restaurants_details');
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$result[] = $this->db->get()->result();	
		
		return $result;		
		
	}

	function list_all_services_by_user_as_array($id)
	{
		$this->db->select('*');
		$this->db->from('accomodation_listing');
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$acco_array = $this->db->get()->result_array();	
		foreach ($acco_array as $acco) {
			$this->db->select('title ');
			$this->db->from('attachments');
			$this->db->where('parent',$acco['accomodation_listing_id']);
			$this->db->where('type','accomodation');
			$details = $this->db->get()->row_array();
			$acco['image'] = $details['title'];
			$result['accomodation'][] = $acco;
		}

		$this->db->select('*');
		$this->db->from('activities_sports');	
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$activity_array = $this->db->get()->result_array();		
		foreach ($activity_array as $activity) {
			$this->db->select('title ');
			$this->db->from('attachments');
			$this->db->where('parent',$activity['activities_sports_id']);
			$this->db->where('type','activities_sport');
			$details1 = $this->db->get()->row_array();
			$activity['image'] = $details1['title'];
			$result['activities'][] = $activity;
		}

		$this->db->select('*');
		$this->db->from('other_listing');
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$other_array = $this->db->get()->result_array();		
		foreach ($other_array as $other) {
			$this->db->select('title ');
			$this->db->from('attachments');
			$this->db->where('parent',$other['other_listing_id']);
			$this->db->where('type','other_listing');
			$details2 = $this->db->get()->row_array();
			$other['image'] = $details2['title'];
			$result['other'][] = $other;
		}

		$this->db->select('*');
		$this->db->from('rental_charters');
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$rental_array = $this->db->get()->result_array();		
		foreach ($rental_array as $rental) {
			$this->db->select('title ');
			$this->db->from('attachments');
			$this->db->where('parent',$rental['rental_charters_id']);
			$this->db->where('type','rental_charter');
			$details3 = $this->db->get()->row_array();
			$rental['image'] = $details3['title'];
			$result['rental'][] = $rental;
		}

		$this->db->select('*');
		$this->db->from('restaurants_details');
		$this->db->where('fk_provider_id',$id);
		$this->db->where('is_active',1);
		$restaurant_array = $this->db->get()->result_array();	
		foreach ($restaurant_array as $restaurant) {
			$this->db->select('title ');
			$this->db->from('attachments');
			$this->db->where('parent',$restaurant['restaurant_id']);
			$this->db->where('type','restaurant');
			$details4 = $this->db->get()->row_array();
			$restaurant['image'] = $details4['title'];
			$result['restaurant'][] = $restaurant;
		}
		
		return $result;		
		
	}

	function list_service_by_date($id,$per_page='')
	{
		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$query = $this->db->query('
			(SELECT activities_sports_id as id,service_title, service_location, country, service_highlights as highlights,is_paid, web_link, web_link_on_page, "activities_sport" as type 
				FROM activities_sports
				WHERE fk_provider_id = '.$id.' AND is_active = 1) 
			UNION ALL 
			(SELECT accomodation_listing_id as id, "Accomodation" as service_title, service_location, country, description as highlights, is_paid, web_link, web_link_on_page, "accomodation" as type
				FROM accomodation_listing
				WHERE fk_provider_id = '.$id.' AND is_active = 1)
			UNION ALL
			(SELECT other_listing_id as id, service_title, service_location, country, service_highlights as highlights,is_paid, web_link, web_link_on_page, "other_listing" as type
				FROM other_listing
				WHERE fk_provider_id = '.$id.' AND is_active = 1)
			UNION ALL
			(SELECT rental_charters_id as id, service_title, service_location, country, service_highlights as highlights, is_paid, web_link, web_link_on_page, "rental_charter" as type
				FROM rental_charters
				WHERE fk_provider_id = '.$id.' AND is_active = 1)
			UNION ALL
			(SELECT restaurant_id as id, service_title, service_location, country, service_highlights as highlights, is_paid, web_link, web_link_on_page, "restaurant" as type
				FROM restaurants_details
				WHERE fk_provider_id = '.$id.' AND is_active = 1)
			LIMIT '.$page.','.$per_page.''
			);
		
		$result = $query->result_array();
		foreach ($result as $value) {
			$this->db->select('title ');
			$this->db->from('attachments');
			$this->db->where('parent',$value['id']);
			$this->db->where('type',$value['type']);
			$details = $this->db->get()->row_array();
			$value['image'] = $details['title'];
			$result1[] = $value;
		}
		return $result1;
	}

	function count_service_by_id($id)
	{
		$query = $this->db->query('
			(SELECT activities_sports_id as id 
				FROM activities_sports
				WHERE fk_provider_id = '.$id.' AND is_active = 1) 
			UNION ALL 
			(SELECT accomodation_listing_id as id
				FROM accomodation_listing
				WHERE fk_provider_id = '.$id.' AND is_active = 1) 
			UNION ALL
			(SELECT other_listing_id as id
				FROM other_listing
				WHERE fk_provider_id = '.$id.' AND is_active = 1)
			UNION ALL
			(SELECT rental_charters_id as id
				FROM rental_charters
				WHERE fk_provider_id = '.$id.' AND is_active = 1)
			UNION ALL
			(SELECT restaurant_id as id
				FROM restaurants_details
				WHERE fk_provider_id = '.$id.' AND is_active = 1)
			'
			);
		$result = $query->result_array();
		return sizeof($result);
	}

	function get_activity_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('activities_sports');	
		$this->db->where('activities_sports_id',$id);
		$this->db->where('is_active',1);
		return $this->db->get()->row_array();	
	}

	function get_activity_search_results_count()
    {
    	$country = $this->session->userdata('country_search');
        $keyword = $this->session->userdata('search_keyword');
        $this->db->select('*');
        $this->db->from('activities_sports');
        $where = "";
        
        $where .= "((activities_sports.service_location like '%$keyword%' OR activities_sports.service_title like '%$keyword%') AND (activities_sports.country like '%$country%'))";


        $this->db->where($where);
        $this->db->where('is_active',1);
        $this->db->order_by("activities_sports.created_on", "asc");
        $query = $this->db->get();
        return $query->num_rows();
    }

    function get_activity_search_results($limit, $offset)
    {
    	$country = $this->session->userdata('country_search');
        $keyword =$this->session->userdata('search_keyword');
        $this->db->select('*');
        $this->db->from('activities_sports');
        $where = "";
        
       //$where .= "(activities_sports.service_location like '%$keyword%' OR activities_sports.service_title like '%$keyword%')";
        $where .= "((activities_sports.service_location like '%$keyword%' OR activities_sports.service_title like '%$keyword%') AND (activities_sports.country like '%$country%'))";

        $this->db->where($where);
        $this->db->where('is_active',1);
        $this->db->order_by("activities_sports.created_on", "asc");
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        $results = $query->result();
        foreach ($results as $result)
        {
            $this->db->select('title as image');
            $this->db->from('attachments');
            $this->db->where('parent', $result->activities_sports_id);
            $this->db->where('type', 'activities_sport');
            $image = $this->db->get()->row();
            if ($image)
            {
                $result->image = $image->image;
            } else
            {
                $result->image = '';
            }

            $this->db->select('sum(feedbacks.rate)/count(feedbacks.rate) as average');
            $this->db->from('feedbacks');
            $this->db->where('parent_id', $result->activities_sports_id);
            $this->db->where('type', 'activities_sports');
            $average = $this->db->get()->row();

            $result->average = $average->average;
            $result = (object) $result;
        }

        return $results;
    }

    function get_details($type,$id)
    {
    	if($type == 'activities_sports'){
    		$table = 'activities_sports';
    	}elseif ($type == 'accomodation') {
    		$table = 'accomodation_listing';
    	}elseif ($type == 'other_listing') {
    		$table = 'other_listing';
    	}elseif ($type == 'rental_charter') {
    		$table = 'rental_charters';
    	}

    	$this->db->select('*');
    	$this->db->from($table);
    	$this->db->where($table.'_id',$id);
    	return $this->db->get()->row_array();
    }
}