<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class TransactionalApiController extends REST_Controller
{

    function __construct($config = 'rest')
    {
        parent::__construct($config);
        date_default_timezone_set("Asia/Jakarta");
        $this->dateToday = date("Y-m-d H:i:s");
        $this->timeToday = date("h:i:s");
        $this->load->model("TransactionalApiModel");
    }
    
    function perbaruiReferal_post(){
        $kode_referal = random_string('alnum', 6);
        $id_user = $this->input->post('id_user');

        $data = array(
            'kode_referal' => $kode_referal
        );

        $result = $this->TransactionalApiModel->updateReferal($data, $id_user);

        if($result){
            $data['id_user'] = $id_user;
            $this->response(array('status' => 200, 'message' => 'Kode Referal Berhasil Diperbarui', 'data' => $data));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Peserta tidak ditemukan! Kode Referal Gagal Diperbarui', 'data' => null));
        }
    }
}