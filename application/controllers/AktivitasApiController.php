<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class AktivitasApiController extends REST_Controller {
  function __construct($config = 'rest') {
    parent::__construct($config);
    date_default_timezone_set("Asia/Jakarta");
    $this->dateToday = date("Y-m-d");
    $this->timeToday = date("h:i:s");
    $this->load->model("GeneralApiModel");
  }

  function dataAktivitas_post(){
    $user = array(
      'id' => $this->input->post('id_user')
    );

    if(!empty($user['id'])){
      $user = $this->GeneralApiModel->getWhereTransactional($user, "user_provinsi_kota")->row();
      if(!empty($user)){
        $aktivitas = $this->GeneralApiModel->getWhereMaster(array('statusdata'=>0), "masterdata_aktivitas")->result();

        $list_aktivitas = array();
        $list_soal = array();
        $list_jawaban = array();

        foreach ($aktivitas as $kd => $vd) {
          $soal = $this->GeneralApiModel->getWhereMaster(array('id_aktivitas'=>$vd->id), "masterdata_soal_aktivitas")->result();
          foreach ($soal as $ks => $vs) {
            $jawaban = $this->GeneralApiModel->getWhereMaster(array('id_soal'=>$vs->id), "masterdata_pilihan_jawaban_aktivitas")->result();
            foreach ($jawaban as $kj => $vj) {
              array_push($list_jawaban, array("id_jawaban"=>$vj->id, "jawaban"=>$vj->jawaban));
            }
            array_push($list_soal, array("id_soal"=>$vs->id, "soal"=>$vs->soal, "tipe"=>$vs->tipe, "data_jawaban"=>$list_jawaban));
            $list_jawaban = array();
          }
          array_push($list_aktivitas, array("id_aktivitas"=>$vd->id, "nama"=>$vd->nama, "list_soal"=>$list_soal));
          $list_soal = array();
        }

        $this->response(array('status' => 200, 'message' => 'Sukses', 'data' => $list_aktivitas));
      }else{
        $this->response(array('status' => 200, 'message' => 'Data User tidak ditemukan', 'data' => null));
      }
    }else{
      $this->response(array('status' => 200, 'message' => 'Masukkan id user terlebih dahulu! data tidak ditemukan', 'data' => null));
    }
  }

  function submitAktivitas_post(){
    $data = json_decode($this->input->raw_input_stream, TRUE);
    $id_user = $data["id_user"];
    $id_pelatihan = $data["id_pelatihan"];
    $data_aktivitas = $data["data_aktivitas"];

    $user = $this->GeneralApiModel->getWhereTransactional(array('id' => $id_user),'transactional_user')->result();

    $list_aktivitas = array();
    if(!empty($id_pelatihan) && !empty($user)){
      foreach ($data_aktivitas as $ka => $va) {
        $id_aktivitas = $va["id_aktivitas"];
        foreach ($va["data_soal"] as $ks => $vs) {
          $id_soal = $vs["id_soal"];
          foreach ($vs["data_jawaban"] as $kb => $vb) {
            $id_jawaban = $vb["id_jawaban"];
            $nilai = $vb["nilai"];
            array_push($list_aktivitas, array("id_pelatihan"=>$id_pelatihan, "id_user"=>$id_user, "id_aktivitas"=>$id_aktivitas, "id_soal"=>$id_soal, "id_jawaban"=>$id_jawaban, "nilai"=>$nilai));
          }
        }
      }
      $status = $this->GeneralApiModel->insertBatchTransactional($list_aktivitas, "transactional_hasil_aktivitas");
      if ($status) {
        $this->response(array('status' => 200, 'message' => 'sukses isi laporan', 'data'=>true));
      } else {
        $this->response(array('status' => 200, 'message' => 'gagal isi laporan', 'data'=>false));
      }
    }else{
      $this->response(array('status' => 200, 'message' => 'Masukkan id user dan id pelatihan terlebih dahulu! data tidak ditemukan', 'data' => $data));
    }
  }
}
