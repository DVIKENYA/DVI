<?php
class Dashboard extends MY_Controller 
{

function __construct() {
parent::__construct();
Modules::run('secure_tings/is_logged_in');

}



function home() {
  Modules::run('secure_tings/is_logged_in');
  //$data['chart'] = $this->get_chart();
  $data['wastage'] = $this->get_wastage();
  $data['mavaccine'] = $this->vaccines();
  //$data['coverage'] = $this->get_coverage();
  $data['section'] = "DVI Kenya";
  $data['subtitle'] = "Dashboard";
  $user_level=$this->session->userdata['logged_in']['user_level'];
  //$data['page_title'] = "Baringo County";
   if($user_level!=='1'){
       $data['view_file'] = "dashboard_view";
    } else if($user_level=='1'){
       $data['view_file'] = "national_dashboard_view";
    }else if($user_level=='5'){
       $data['view_file'] = "facility_dashboard_view";
    }

  $data['module'] = "dashboard";
  $data['id'] = ($this->session->userdata['logged_in']['user_id']);
  $data['user_level'] = ($this->session->userdata['logged_in']['user_level']);
  $data['user_object'] = $this->get_user_object();
  $data['main_title'] = $this->get_title();
  echo Modules::run('template/'.$this->redirect($this->session->userdata['logged_in']['user_group']), $data);

}


function get_init(){
  $this->load->model('mdl_dashboard');
  $query = $this->mdl_dashboard->initWastage();
  $json_array = array();
  foreach ($query->result() as $row) {
    $data['name'] = $row->Vaccine_name;
    $data['y'] = (int)$row->Wastage_factor;

    array_push($json_array, $data);
  }

  echo json_encode($json_array);
}

function get_chart() {
    $user_id = ($this->session->userdata['logged_in']['user_id']);
    $this->load->model('mdl_dashboard');
    $query = $this->mdl_dashboard->getChart($user_id);
    $json_array=array(); 
    foreach ($query->result() as $row) {
       $data['name'] = $row->Vaccine;
       $data['y'] = (float)$row->Stock_balance;
       
       array_push($json_array,$data);
    }    
   echo json_encode($json_array);
  }


  function get_coverage() {
    $this->load->model('mdl_dashboard');
    $user_id = $this->session->userdata['logged_in']['user_id'];
    $user_level=$this->session->userdata['logged_in']['user_level'];
     if($user_level=='3' || $user_level=='2'){
    $query = $this->mdl_dashboard->get_county_coverage($user_id);
     } else if($user_level=='4'){
    $query = $this->mdl_dashboard->get_subcounty_coverage($user_id);
     }else if($user_level=='5'){
    $query = $this->mdl_dashboard->get_subcounty_coverage($user_id);
     }else if($user_level=='1'){
    $query = $this->mdl_dashboard->get_national_coverage($user_id);
     }
     $json_array=array();
    foreach ($query->result() as $row) {
      $data['name'] = $row->Months;
      $data['y'] = (int)$row->BCG;

     // $json= array(
     /* "name"=>$row->Months,
      "data"=>array(["BCG"=>(int)$row->BCG,"DPT2"=>(int)$row->DPT2,"DPT3"=>(int)$row->DPT3,
                     "MEASLES"=>(int)$row->MEASLES,"OPV"=>(int)$row->OPV,"OPV1"=>(int)$row->OPV1,
                     "OPV2"=>(int)$row->OPV2,"OPV3"=>(int)$row->OPV3,"PCV1"=>(int)$row->PCV1,
                     "PCV2"=>(int)$row->PCV2,"PCV3"=>(int)$row->PCV3,"ROTA1"=>(int)$row->ROTA1,
                     "ROTA2"=>(int)$row->ROTA2]));    */
    //"data"=>array("$row->Months"=>(int)$row->BCG));

    array_push($json_array,$data);

    }
    echo json_encode($json_array);
    //return $json_array;

  }

function get_wastage() {
    $this->load->model('mdl_dashboard');
    $user_id = $this->session->userdata['logged_in']['user_id'];
    $user_level=$this->session->userdata['logged_in']['user_level'];
    
    if($user_level=='3' || $user_level=='2'){
    $query = $this->mdl_dashboard->get_county_wastage($user_id);
    } else if($user_level=='4'){
    $query = $this->mdl_dashboard->get_subcounty_wastage($user_id);
    }else if($user_level=='5'){
    $query = $this->mdl_dashboard->get_facility_wastage($user_id);
    }else if($user_level=='1'){
    $query = $this->mdl_dashboard->get_national_wastage($user_id);
    }
   
    foreach ($query as $row) {
      $json_array= array(
      (int)$row->BCG,
      (int)$row->DPT,
      (int)$row->MEASLES,
      (int)$row->OPV,
      (int)$row->PCV,
      '',
      '',
      (int)$row->YELLOWFEVER
       );
      }
   //echo json_encode($json_array);
   return $json_array;
  }


 
function get_linechart(){
    $this->load->model('mdl_dashboard');
    $query = $this->mdl_dashboard->get_linechart();
    $json_array=array(); 
    foreach ($query as $row) {

       $data['name'] = $row->Vaccine;
       $data['y'] = (int)$row->Stock_balance;

       $json_array[] = $data;

    }
    //return $json_array;
    echo json_encode($json_array);
}


function get_data() {
    $query = $this->getData();
    //var_dump($query);
    $datatable = array();
    $no = $_POST['start'];
    foreach ($query as $data) {
      $no++;
      $row = array();
      $row[] = $data->Months;
      $row[] = (int)$data->Above2yrs;
      $row[] = $data->Above1yr;
     
      $datatable[] = $row;
    }
    
    $output = array(
              "draw" => $_POST['draw'],
              "recordsTotal" => $this->count_all(),
              "recordsFiltered" => $this->count_filtered(),
              "data" => $datatable,
            );
    //output to json format
    echo json_encode($output);
  }


function vaccines(){
    $query = $this->mdl_dashboard->get_vaccine_details();
    $vaccines=array(); 
    foreach ($query->result() as $row) {
       $data['ID'] = (int)$row->ID;
       $data['Vaccine_name'] = $row->Vaccine_name;

       array_push($vaccines,$data);

    }
        
    return $vaccines;
        }


function getData() {
    $this->load->model('mdl_dashboard');
    $query = $this->mdl_dashboard->getDatatable();
    return $query;
    //var_dump($query);
  }

function get($order_by){
$this->load->model('mdl_dashboard');
$query = $this->mdl_dashboard->get($order_by);
return $query;
}

function get_with_limit($limit, $offset, $order_by) {
$this->load->model('mdl_dashboard');
$query = $this->mdl_dashboard->get_with_limit($limit, $offset, $order_by);
return $query;
}

function get_where($id){
$this->load->model('mdl_dashboard');
$query = $this->mdl_dashboard->get_where($id);
return $query;
}

function get_where_custom($col, $value) {
$this->load->model('mdl_dashboard');
$query = $this->mdl_dashboard->get_where_custom($col, $value);
return $query;
}

function _insert($data){
$this->load->model('mdl_dashboard');
$this->mdl_dashboard->_insert($data);
}

function _update($id, $data){
$this->load->model('mdl_dashboard');
$this->mdl_dashboard->_update($id, $data);
}

function _delete($id){
$this->load->model('mdl_dashboard');
$this->mdl_dashboard->_delete($id);
}

function count_where($column, $value) {
$this->load->model('mdl_dashboard');
$count = $this->mdl_dashboard->count_where($column, $value);
return $count;
}

function get_max() {
$this->load->model('mdl_dashboard');
$max_id = $this->mdl_dashboard->get_max();
return $max_id;
}

function _custom_query($mysql_query) {
$this->load->model('mdl_dashboard');
$query = $this->mdl_dashboard->_custom_query($mysql_query);
return $query;
}

function count_all() {
            $this->load->model('mdl_dashboard');
            $query = $this->mdl_dashboard->count_all();
            return $query;
      }

function count_filtered() {
            $this->load->model('mdl_dashboard');
            $query = $this->mdl_dashboard->count_filtered();
            return $query;
      }



}



