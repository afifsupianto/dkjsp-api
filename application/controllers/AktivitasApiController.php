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

    $id_grading = $this->input->post('id_grading');

    if(!empty($user['id'])){
      $user = $this->GeneralApiModel->getWhereTransactional($user, "user_provinsi_kota")->row();
      if(!empty($user)){
        $aktivitas = $this->GeneralApiModel->getWhereMaster(array('id_grading'=>$id_grading), "masterdata_aktivitas")->result();

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

        $this->response(array('status' => 200, 'message' => 'Sukses', 'data' => $list_aktivitas[0]));
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
    $id_kelas = $data["id_kelas"];
    $id_pelatihan = $data["id_pelatihan"];
    $data_soal = $data["data_soal"];
    $id_aktivitas = $data["id_aktivitas"];

    $user = $this->GeneralApiModel->getWhereTransactional(array('id' => $id_user),'transactional_user')->result();

    $list_aktivitas = array();
    if(!empty($id_pelatihan) && !empty($user)){
    //   foreach ($data_aktivitas as $ka => $va) {
        foreach ($data_soal as $ks => $vs) {
          $id_soal = $vs["id_soal"];
          foreach ($vs["data_jawaban"] as $kb => $vb) {
            $id_jawaban = $vb["id_jawaban"];
            $nilai = $vb["nilai"];
            array_push($list_aktivitas, array("id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan, "id_user"=>$id_user, "id_aktivitas"=>$id_aktivitas, "id_soal"=>$id_soal, "id_jawaban"=>$id_jawaban, "nilai"=>$nilai));
          }
        }
    //   }
      $status = $this->GeneralApiModel->insertBatchTransactional($list_aktivitas, "transactional_hasil_aktivitas");
      if ($status) {
        $this->response(array('status' => 200, 'message' => 'sukses isi laporan', 'data'=>true));
      } else {
        $this->response(array('status' => 200, 'message' => 'gagal isi laporan', 'data'=>false));
      }
    }else{
      $this->response(array('status' => 200, 'message' => 'Masukkan id user dan id pelatihan terlebih dahulu! data tidak ditemukan', 'data' => false));
    }
  }

  function aktivitasPeserta_post(){
    $time_expired=60*60*24*3;
    $time_aweek=$time_expired*2;
    header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
    $id_user = $this->input->post('id_user');
    $id_kelas = $this->input->post('id_kelas');
    $id_pelatihan = $this->input->post('id_pelatihan');

    if(!empty($id_pelatihan) && !empty($id_user)){
      $semua_kondisi = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_hasil_skrining")->result();
      $kondisi = ($semua_kondisi?$semua_kondisi[0]:null);
      $semua_presensi = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_presensi")->result();
      $presensi = ($semua_presensi?$semua_presensi[0]:null);

      $kelas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id"=>$id_kelas), "cdate", "DESC", "transactional_kelas")->result()[0];
      $id_pelatihan = $kelas->id_pelatihan;
      $aktivitas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan), "cdate", "DESC", "transactional_hasil_aktivitas")->result();
      $aktivitas = ($aktivitas?$aktivitas[0]:null);

      $list_skrining = array();
      foreach ($semua_kondisi as $k) {
        $nama = $this->GeneralApiModel->getWhereMaster(array('id'=>$k->id_skrining), 'masterdata_skrining')->result()[0]->nama;
        array_push($list_skrining,
          array(
            'nama'=>$nama,
            'hasil_test_terakhir'=>array(
                'kondisi_fisik' => ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_fisik),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
                'kondisi_mental' => ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_mental),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
                'tanggal_isi' => $k->cdate
            )
          )
        );
      }

      $result = array(
        'is_presensi'=>($presensi?($this->date_diff($presensi->cdate)==0?true:false):null),
        'jml_presensi'=>count($semua_presensi),
        'is_aktivitas_harian'=>($aktivitas?($this->date_diff($aktivitas->cdate)==0?true:false):null),
        'kondisi_fisik' => ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_fisik),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
        'kondisi_mental' => ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_mental),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
        'id_grading'=>($kondisi?$kondisi->kondisi_fisik:null),
        'countdown_next'=>$this->date_diff($kondisi->cdate),
        'list_skrining'=>$list_skrining
      );

      $this->response(array('status' => 200, 'message' => 'Data aktivitas peserta berhasil didapatkan', 'data' => $result));
    } else {
      $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan, id user / id kelas / id pelatihan salah!', 'data' => null));
    }
  }

  function date_diff($date){
    $dob = new DateTime($date);
    $now = new DateTime();

    $datetime1 = date_create($dob->format('Y-m-d h:m:s'));
    $datetime2 = date_create($now->format('Y-m-d h:m:s'));

    $interval = date_diff($datetime1, $datetime2);

    $day = $interval->format('%d');
    $hour = $interval->format('%h');

    return array('hari'=>$day, 'jam'=>$hour);
  }
}
