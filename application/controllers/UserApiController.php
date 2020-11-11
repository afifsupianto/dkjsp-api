<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class UserApiController extends REST_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        date_default_timezone_set("Asia/Jakarta");
        $this->dateToday = date("Y-m-d H:i:s");
        $this->timeToday = date("h:i:s");
        $this->load->model("GeneralApiModel");
    }

    /* -------------------------------- CRUD User Peserta ---------------------- */

    function showSemuaUserPeserta_get(){
        $result = $this->GeneralApiModel->getAllTransactional("user_peserta_detail")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data Peserta berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data Peserta yang ditemukan!', 'data' => $result));
        }
    }

    /* -------------------------------- CRUD User Keluarga Binaan ---------------------- */

    function showSemuaUserKeluargaBinaan_get(){
        // $result = $this->GeneralApiModel->getAllTransactional("user_anggotakeluarga_detail")->result();
        $result = $this->GeneralApiModel->getWhereTransactional(array('role'=>1),"user_anggotakeluarga_detail")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data Keluarga Binaan berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data Keluarga Binaan yang ditemukan!', 'data' => $result));
        }
    }

    /* -------------------------------- CRUD User Panitia ---------------------- */

    function showSemuaUserPanitia_get(){
        $result = $this->GeneralApiModel->getAllTransactional("user_panitia_detail")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data Panitia berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data Panitia yang ditemukan!', 'data' => $result));
        }
    }

    /* -------------------------------- CRUD User Kader ---------------------- */

    function showSemuaUserKader_get(){
        $result = true;
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data Kader berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data Kader yang ditemukan!', 'data' => $result));
        }
    }

    /* -------------------------------- CRUD User Admin ---------------------- */

    function showSemuaUserAdmin_get(){
        $result = $this->GeneralApiModel->getAllTransactional("user_admin_detail")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data Admin berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data Admin yang ditemukan!', 'data' => $result));
        }
    }

    /* -------------------------------- CRUD User Operator ---------------------- */

    function showSemuaUserOperator_get(){
        $result = $this->GeneralApiModel->getAllTransactional("user_operator_detail")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data Operator berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data Operator yang ditemukan!', 'data' => $result));
        }
    }

    /* -------------------------------- CRUD User Pemateri ---------------------- */

    function showSemuaUserPemateri_get(){
        $result = $this->GeneralApiModel->getAllTransactional("user_pemateri_detail")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data Pemateri berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data Pemateri yang ditemukan!', 'data' => $result));
        }
    }

    function cekData_post(){
      $tipe = $this->input->post('tipe');
      $nilai = $this->input->post('nilai');

      $result = $this->GeneralApiModel->getWhereTransactional(array($tipe=>$nilai), 'transactional_user')->result();
      if (count($result)>0) {
        $this->response(array('status' => 200, 'message' => 'Data Sudah Ada', 'data' => true));
      } else {
        $this->response(array('status' => 200, 'message' => 'Data Belum Ada', 'data' => false));
      }
    }
}
