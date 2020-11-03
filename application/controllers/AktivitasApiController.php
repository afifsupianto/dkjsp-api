
application/x-httpd-php AktivitasApiController.php ( C++ source, ASCII text )
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
            array_push($list_aktivitas, array("id_pelatihan"=>$id_pelatihan, "id_user"=>$id_user, "id_aktivitas"=>$id_aktivitas, "id_soal"=>$id_soal, "id_jawaban"=>$id_jawaban, "nilai"=>$nilai));
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
    $id_kelas = array(
      'id_kelas' => $this->input->post('id_kelas'),
      'id_user' => $this->input->post('id_user')
    );
    $id_user = $this->input->post('id_user');
    $id_pelatihan = $this->input->post('id_pelatihan');

    if(!empty($id_pelatihan) && !empty($id_user)){
      $kondisi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_hasil_skrining")->result();
      $kondisi = ($kondisi?$kondisi[0]:null);
      $presensi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_presensi")->result();
      $presensi = ($presensi?$presensi[0]:null);

      // $kelas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id"=>$id_kelas), "cdate", "DESC", "transactional_kelas")->result()[0];
      // $id_pelatihan = $kelas->id_pelatihan;
      $aktivitas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user, "id_pelatihan"=>$id_pelatihan), "cdate", "DESC", "transactional_hasil_aktivitas")->result();
      $aktivitas = ($aktivitas?$aktivitas[0]:null);

      $binaan = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_pembina"=>$id_user, "id_kelas"=>$id_kelas), "cdate", "ASC", " transactional_binaan")->result();
      $list_binaan = array();
      foreach ($binaan as $b) {
        $no_kk = $b->nomor_kk;
        $daftar = $this->GeneralApiModel->getWhereTransactional(array("nomor_kk"=>$no_kk), 'user_anggotakeluarga_detail')->result()[0];
        array_push($list_binaan, array("nomor_kk"=>$no_kk, "nama"=>$daftar->namalengkap));
      }

      $kader = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_pembina"=>$id_user, "id_kelas"=>$id_kelas, "role"=>1), "cdate", "ASC", " transactional_kode_referal")->result();
      $list_kader = array();
      foreach ($kader as $b) {
        $id_user = $b->id_user;
        $daftar = $this->GeneralApiModel->getWhereTransactional(array("id"=>$id_user), 'transactional_user')->result()[0];
        array_push($list_kader, array("id_user"=>$id_user, "nama"=>$daftar->namalengkap));
      }

      $list_materi = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_kelas"=>$id_kelas),"id_materi","ASC","list_materi_jadwal")->result();

      $i = 0;
      foreach($list_materi as $row){
        $materi['list_materi'][$i]['id'] = $row->id_materi;
        $materi['list_materi'][$i]['judul_materi'] = $row->judul_materi;
        $materi['list_materi'][$i]['jumlah_subbab'] = $row->jml_subbab;
        $materi['list_materi'][$i]['jumlah_subbab_selesai'] = $row->jml_subbab_selesai;
        if($row->status_buka == 1){
          if(($row->jml_subbab == $row->jml_subbab_selesai) && $row->jml_subbab != 0){
            $materi['list_materi'][$i]['status'] = 2;
          } else {
            $materi['list_materi'][$i]['status'] = 1;
          }
        } else {
          $materi['list_materi'][$i]['status'] = 0;
        }
        $materi['list_materi'][$i]['list_subbab'] = array();

        //foreach untuk set daftar list subbab
        $id_materi = array(
          'id_materi' => $row->id_materi
        );
        $result2 = $this->GeneralApiModel->getWhereMaster($id_materi, "masterdata_subbab_materi")->result();
        $j = 0;
        foreach($result2 as $row){
          $materi['list_materi'][$i]['list_subbab'][$j]['id_subbab'] = $row->id;
          $materi['list_materi'][$i]['list_subbab'][$j]['judul_subbab'] = $row->judul;
          $materi['list_materi'][$i]['list_subbab'][$j]['is_test'] = $row->is_test;
          $j++;
        }
        $i++;
      }

      $result = array(
        'kondisi_fisik' => ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_fisik),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
        'kondisi_mental' => ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_mental),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
        'id_grading'=>($kondisi?$kondisi->kondisi_fisik:null),
        'skrining_terakhir'=> array(
          'id'=>($kondisi?$kondisi->id_skrining:null),
          'is_sudah_skrining'=>($kondisi?($this->date_diff($kondisi->cdate)<=14?true:false):null)
        ),
        'presensi_terakhir' => array(
          'waktu'=>($presensi?$presensi->cdate:null),
          'is_sudah_presensi'=>($presensi?($this->date_diff($presensi->cdate)==0?true:false):null)
        ),
        'laporan_harian_terakhir' => array(
          'id'=> ($aktivitas?$aktivitas->id_aktivitas:null),
          'waktu'=>($aktivitas?$aktivitas->cdate:null),
          'is_sudah_isi_laporan'=>($aktivitas?($this->date_diff($aktivitas->cdate)==0?true:false):null)
        ),
        'list_materi' => $materi,
        'list_keluargabinaan' => $list_binaan,
        'list_kader' => $list_kader
      );

      $this->response(array('status' => 200, 'message' => 'Data home peserta berhasil didapatkan', 'data' => $result));
    } else {
      $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan, id user / id kelas salah!', 'data' => null));
    }
  }
}
