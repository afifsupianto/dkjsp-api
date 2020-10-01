<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class AdminApiController extends REST_Controller
{

    function __construct($config = 'rest')
    {
        parent::__construct($config);
        date_default_timezone_set("Asia/Jakarta");
        $this->dateToday = date("Y-m-d H:i:s");
        $this->timeToday = date("h:i:s");
        $this->load->model("AdminApiModel");
        $this->load->model("GeneralApiModel");
    }

    function getAdminDashboard_get(){
        $time_expired=60*60*24*3;
        $time_aweek=$time_expired*2;
        header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
        $data = array(
            'jumlah_user_kelas' => array()
        );
        $result = $this->AdminApiModel->getDataJumlahUserKelas();
        $data['jumlah_user_kelas'] = $result;
        $result = $this->AdminApiModel->getDataJumlahUserProvinsi();
        $data['jumlah_user_provinsi'] = $result;
        $result = $this->AdminApiModel->getDataJumlahUserKota();
        $data['jumlah_user_kota'] = $result;
        $this->response(array('status' => 200, 'message' => 'Data dashboard admin berhasil didapatkan', 'data' => $data));
    }
}