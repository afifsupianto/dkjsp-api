<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class PesertaApiController extends REST_Controller
{

  function __construct($config = 'rest')
  {
    parent::__construct($config);
    date_default_timezone_set("Asia/Jakarta");
    $this->dateToday = date("Y-m-d H:i:s");
    $this->timeToday = date("h:i:s");
    $this->load->model("AktivitasApiModel");
    $this->load->model("PesertaApiModel");
    $this->load->model("GeneralApiModel");
  }

  function homePeserta_post(){
    $time_expired=60*60*24*3;
    $time_aweek=$time_expired*2;
    header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
    $where = array(
      'id_kelas' => $this->input->post('id_kelas'),
      'id_user' => $this->input->post('id_user')
    );
    $id_user = $this->input->post('id_user');
    $id_kelas = $this->input->post('id_kelas');

    if(!empty($id_kelas) && !empty($id_user)){
      $kondisi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_hasil_skrining")->result();
      $kondisi = ($kondisi?$kondisi[0]:null);
      $presensi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_presensi")->result();
      $presensi = ($presensi?$presensi[0]:null);

      $kelas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id"=>$id_kelas), "cdate", "DESC", "transactional_kelas")->result()[0];
      $id_pelatihan = $kelas->id_pelatihan;
      $aktivitas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan), "cdate", "DESC", "transactional_hasil_aktivitas")->result();
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

      $list_materi = $this->GeneralApiModel->getWhereTransactionalOrdered($where,"id_materi","ASC","list_materi_jadwal")->result();

      $i = 0;
      foreach($list_materi as $row){
        $materi['list_materi'][$i]['id'] = $row->id_materi;
        $materi['list_materi'][$i]['judul_materi'] = $row->judul_materi;
        $materi['list_materi'][$i]['jumlah_subbab'] = $row->jml_subbab;
        $materi['list_materi'][$i]['jumlah_subbab_selesai'] = $row->jml_subbab_selesai;
        if($row->status_buka == 1){
          if(($row->jml_subbab == $row->jml_subbab_selesai) && $row->jml_subbab <= 0){
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
          // 'is_sudah_presensi'=>$this->date_diff($presensi->cdate)
        ),
        'laporan_harian_terakhir' => array(
          'id'=> ($aktivitas?$aktivitas->id_aktivitas:null),
          'waktu'=>($aktivitas?$aktivitas->cdate:null),
          'is_sudah_isi_laporan'=>($aktivitas?($this->date_diff($aktivitas->cdate)==0?true:false):null)
        ),
        'list_materi' => $materi["list_materi"],
        // 'list_materi' => count($list_materi),
        'list_keluargabinaan' => $list_binaan,
        'list_kader' => $list_kader
      );

      $this->response(array('status' => 200, 'message' => 'Data home peserta berhasil didapatkan', 'data' => $result));
    } else {
      $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan, id user / id kelas salah!', 'data' => null));
    }
  }

  function menuMateri_post(){
    $time_expired=60*60*24*3;
    $time_aweek=$time_expired*2;
    header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
    $id_kelas = array(
      'id_pelatihan' => $this->input->post('id_pelatihan'),
      'id_kelas' => $this->input->post('id_kelas'),
      'id_user' => $this->input->post('id_user')
    );
    $id_user = $this->input->post('id_user');
    $id_pelatihan = $this->input->post('id_pelatihan');


    if(!empty($id_kelas['id_kelas']) && !empty($id_kelas['id_user']) && !empty($id_kelas['id_pelatihan'])){
      $data = array();
      $result = $this->GeneralApiModel->getWhereTransactional(array("id_kelas" => $this->input->post('id_kelas')), "kelas_pelatihan")->row();
      if(!empty($result)){
        $histori_test = $this->GeneralApiModel->getWhereTransactionalOrdered(array('id_user'=>$id_user, 'id_kelas'=>$id_kelas['id_kelas'], 'id_pelatihan'=>$id_pelatihan),'cdate','ASC','transactional_test')->result();
        $list_test = array();
        $tot_nilai = 0;
        foreach ($histori_test as $h) {
          $judul = $this->GeneralApiModel->getWhereMaster(array('id'=>$h->id_materi),'masterdata_materi')->result()[0]->judul;
          $tot_nilai+=$h->jumlah_benar;
          array_push($list_test, array('id'=>$h->id, 'judul_materi'=>$judul, 'skor_akhir'=>$h->jumlah_benar, 'tgl_buat'=>$h->cdate));
        }

        $data['deskripsi_pelatihan'] = $result->deskripsi_pelatihan;
        $data['judul_materi_terakhir'] = $judul;
        $data['jumlah_anggota_kelas'] = $result->jumlah_peserta; //buat view counting di kode referal where role = 0
        $data['nama_kelas'] = $result->nama_kelas;
        $data['nilai_rata_seluruh_test'] = ($tot_nilai/count($histori_test));
        $tgl_buka = date("d-m-Y", strtotime($result->tgl_buka));
        $tgl_selesai = date("d-m-Y", strtotime($result->tgl_selesai));
        $data['periode_kelas'] = strval($tgl_buka)." - ".strval($tgl_selesai); //tgl buka - tgl selesai
        $data['status_kelas'] = $result->status_kelas;
        $data['list_riwayat_test'] = $list_test;
        $data['list_materi'] = array();

        $result = $this->GeneralApiModel->getWhereTransactionalOrdered($id_kelas,"id_materi","ASC","list_materi_jadwal")->result();
        $i = 0;
        //foreach untuk set daftar materi
        foreach($result as $row){
          $data['list_materi'][$i]['id'] = $row->id_materi;
          $data['list_materi'][$i]['judul_materi'] = $row->judul_materi;
          $data['list_materi'][$i]['jumlah_subbab'] = $row->jml_subbab;
          $data['list_materi'][$i]['jumlah_subbab_selesai'] = $row->jml_subbab_selesai;
          if($row->status_buka == 1){
            if(($row->jml_subbab == $row->jml_subbab_selesai) && $row->jml_subbab <= 0){
              $data['list_materi'][$i]['status'] = 2;
            }else{
              $data['list_materi'][$i]['status'] = 1;
            }
          }
          else{
            $data['list_materi'][$i]['status'] = 0;
          }
          $data['list_materi'][$i]['list_subbab'] = array();

          //foreach untuk set daftar list subbab
          $id_materi = array(
            'id_materi' => $row->id_materi
          );
          $result2 = $this->GeneralApiModel->getWhereMaster($id_materi, "masterdata_subbab_materi")->result();
          $j = 0;
          foreach($result2 as $row){
            $data['list_materi'][$i]['list_subbab'][$j]['id_subbab'] = $row->id;
            $data['list_materi'][$i]['list_subbab'][$j]['judul_subbab'] = $row->judul;
            $data['list_materi'][$i]['list_subbab'][$j]['is_test'] = $row->is_test;
            $j++;
          }
          $i++;
        }
        $data['jml_materi_dipelajari'] = $i;
        $this->response(array('status' => 200, 'message' => 'data diterima', 'data' => $data));
      }else{
        $this->response(array('status' => 200, 'message' => 'pencarian kelas berdasarkan id tidak ditemukan', 'data' => null));
      }
    }else{
      $this->response(array('status' => 200, 'message' => 'inputan id kelas tidak ditemukan!', 'data' => null));
    }
  }

  function lihatMateri_post(){
    $id_kelas = array(
      'id_kelas' => $this->input->post('id_kelas'),
      'id_user' => $this->input->post('id_user')
    );
    if(!empty($id_kelas['id_kelas']) && !empty($id_kelas['id_user'])){
      $data = array();
      $result = $this->GeneralApiModel->getWhereTransactional($id_kelas, "list_materi_jadwal")->result();
      $i = 0;
      //foreach untuk set daftar materi
      foreach($result as $row){
        $data[$i]['id'] = $row->id_materi;
        $data[$i]['judul_materi'] = $row->judul_materi;
        $data[$i]['jumlah_subbab'] = $row->jml_subbab;
        $data[$i]['jumlah_subbab_selesai'] = $row->jml_subbab_selesai;
        if($row->status_buka == 1){
          if(($row->jml_subbab == $row->jml_subbab_selesai) && $row->jml_subbab != 0){
            $data[$i]['status'] = 2;
          }else{
            $data[$i]['status'] = 1;
          }
        }
        else{
          $data[$i]['status'] = 0;
        }
        $data[$i]['list_subbab'] = array();

        //foreach untuk set daftar list subbab
        $id_materi = array(
          'id_materi' => $row->id_materi
        );
        $result2 = $this->GeneralApiModel->getWhereMaster($id_materi, "masterdata_subbab_materi")->result();
        $j = 0;
        foreach($result2 as $row){
          $data[$i]['list_subbab'][$j]['id_subbab'] = $row->id;
          $data[$i]['list_subbab'][$j]['judul_subbab'] = $row->judul;
          $data[$i]['list_subbab'][$j]['is_test'] = $row->is_test;
          $j++;
        }
        $i++;
      }
      $this->response(array('status' => 200, 'message' => 'Data materi berhasil didapatkan!', 'data' => $data));
    }else{
      $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan!', 'data' => null));
    }
  }

  function detailMateri_post(){
    $id_kelas = array(
      'id_kelas' => $this->input->post('id_kelas'),
      'id_user' => $this->input->post('id_user'),
      'id_materi' => $this->input->post('id_materi')
    );
    if(!empty($id_kelas['id_kelas']) && !empty($id_kelas['id_user']) && !empty($id_kelas['id_materi'])){
      $result = $this->GeneralApiModel->getWhereTransactional($id_kelas, "list_materi_jadwal")->result();
      $data = array(
        'id' => $result[0]->id_materi,
        'judul' => $result[0]->judul_materi,
        'tgl_buka_materi' => $result[0]->tgl_buka_materi,
        'jml_subbab_selesai' => $result[0]->jml_subbab_selesai,
        'link_meet' => $result[0]->link_meet
      );
      $data['list_subbab'] = array();
      $i = 0;
      $where = array(
        'id_user' => $this->input->post('id_user'),
        'id_materi' => $this->input->post('id_materi')
      );
      $result2 = $this->GeneralApiModel->getWhereTransactional($where, "list_subbab_progress")->result();
      foreach($result2 as $row){
        $data['list_subbab'][$i]['id'] = $row->id_subbab;
        $data['list_subbab'][$i]['judul'] = $row->judul_subbab;
        $data['list_subbab'][$i]['is_test'] = $row->is_test;
        $data['list_subbab'][$i]['deskripsi'] = $row->deskripsi;
        $data['list_subbab'][$i]['status'] = $row->status_progress;
        $i++;
      }
      $this->response(array('status' => 200, 'message' => 'test', 'data' => $data));
    }else{
      $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan!', 'data' => null));
    }
  }

  function lihatKontenSubbab_post(){
    $time_expired=60*60*24*3;
    $time_aweek=$time_expired*2;
    header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
    $input = array(
      'id' => $this->input->post('id_subbab')
    );
    if(!empty($input['id'])){
      $result = $this->GeneralApiModel->getWhereMaster($input, "masterdata_subbab_materi")->row();
      $data = array(
        'judul' => $result->judul
        // 'file_url' => $result->file_url
      );
      $data['list_konten'] = array();

      $id_subbab = array(
        'id_subbab' => $this->input->post('id_subbab')
      );
      $i = 0;
      $result2 = $this->GeneralApiModel->getWhereTransactional($id_subbab, "list_konten_subbab")->result();
      foreach($result2 as $row){
        $data['list_konten'][$i]['tipe'] = $row->tipe_konten;
        $data['list_konten'][$i]['isi'] = $row->isi;
        $data['list_konten'][$i]['deskripsi'] = $row->deskripsi;
        $i++;
      }
      $this->response(array('status' => 200, 'message' => 'test', 'data' => $data));
    }else{
      $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan!', 'data' => null));
    }
  }

  function dashboardPelatihanPeserta_post(){
    $time_expired=60*60*24*3;
    $time_aweek=$time_expired*2;
    header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
    $data = array(
      'id_user' => $this->input->post('id_user')
    );
    $result = $this->GeneralApiModel->getWhereTransactional($data, "dashboard_peserta_pelatihan")->result();
    if($result){
      $this->response(array('status' => 200, 'message' => 'Data dashboard pelatihan berhasil ditemukan!', 'data' => $result));
    }
    else{
      $this->response(array('status' => 200, 'message' => 'Tidak ada data ditemukan!', 'data' => array()));
    }
  }

  //cari relawan/peserta
  function cariPeserta_post(){
    $data = array(
      'role' => 0,
      'kode_referal' => $this->input->post('kode_referal')
    );

    $result = $this->PesertaApiModel->getPesertaByReferal($data)->result();
    if($result){
      $this->response(array('status' => 200, 'message' => 'Kader Berhasil ditemukan!', 'data' => $result));
    }
    else{
      $this->response(array('status' => 200, 'message' => 'Kader tidak ditemukan!', 'data' => null));
    }
  }

  function daftarLaporan_post(){
    $id_user = $this->input->post('id_user');
    $id_kelas = $this->input->post('id_kelas');
    $id_pelatihan = $this->input->post('id_pelatihan');

    if(!empty($id_pelatihan) && !empty($id_user) && !empty($id_kelas)){
      $status_keluarga = array("Kepala Keluarga", "Istri", "Anak");
      $anggota = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id"=>$id_user), "role", "DESC", "user_anggotakeluarga_detail")->result();
      $anggota = ($anggota?$anggota[0]:null);
      $kondisi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_hasil_skrining")->result();
      $kondisi = ($kondisi?$kondisi[0]:null);

      // $presensi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_presensi")->result()[0];
      $aktivitas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user, "id_pelatihan"=>$id_pelatihan), "cdate", "DESC", "transactional_hasil_aktivitas")->result();
      $aktivitas = ($aktivitas?$aktivitas[0]:null);

      $histori_presensi = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_presensi")->result();
      $histori_aktivitas = $this->AktivitasApiModel->getAktivitasHarian(array("id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan))->result();
      // $histori_aktivitas = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan), "cdate", "DESC", "transactional_hasil_aktivitas")->result();
      $histori_skrining = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_hasil_skrining")->result();

      // $laporan_aktivitas = array();
      // $total_aktivitas = 0;
      // foreach ($histori_aktivitas as $h) {
        // $total_aktivitas++;
        // array_push($laporan_aktivitas, array('id'=>$h->id_aktivitas, 'cdate'=>$h->cdate));
      // }

      $laporan_skrining = array();
      foreach ($histori_skrining as $h) {
        array_push($laporan_skrining, array(
          'id'=>$h->id,
          'kondisi_fisik'=>$this->GeneralApiModel->getWhereMaster(array('id' => $h->kondisi_fisik),'masterdata_grading_status_kesehatan')->result()[0]->nama,
          'kondisi_mental'=>$this->GeneralApiModel->getWhereMaster(array('id' => $h->kondisi_mental),'masterdata_grading_status_kesehatan')->result()[0]->nama,
          'cdate'=>$h->cdate));
        }

        $result = array(
          'nik'=>($anggota?$anggota->nik_anggota:null),
          'nama'=>($anggota?$anggota->namalengkap:null),
          'status_keluarga'=>($anggota?$status_keluarga[$anggota->status_keluarga]:null),
          'umur'=>($anggota?$this->get_umur($anggota->tgl_lahir):null),
          'kondisi_fisik'=>($kondisi?$this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_fisik),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
          'kondisi_mental'=>($kondisi?$this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_mental),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
          'terakhir_presensi'=>($histori_presensi?$histori_presensi[0]->cdate:null),
          'terakhir_isi_aktivitas'=>($aktivitas?$aktivitas->cdate:null),
          'total_presensi'=>count($histori_presensi),
          'total_laporan'=>count($histori_aktivitas),
          'laporan_aktivitas'=>$histori_aktivitas,
          // 'laporan_aktivitas'=>$laporan_aktivitas,
          'laporan_skrining'=>$laporan_skrining,
        );
        $this->response(array('status' => 200, 'message' => 'Data berhasil didapatkan', 'data' => $result));
      } else {
        $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan, id user / id pelatihan / id kelas tidak tersedia!', 'data' => null));
      }
    }

    function lihatKeluargaPeserta_post(){
      $id_user = $this->input->post('id_user');
      $id_kelas = $this->input->post('id_kelas');
      if(!empty($id_kelas) && !empty($id_user)){
        $list_binaan = array();
        $list_kader = array();
        $kelas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id"=>$id_kelas), "cdate", "DESC", "transactional_kelas")->result()[0];
        if ($kelas) {
          $id_pelatihan = $kelas->id_pelatihan;
          $binaan = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_pembina"=>$id_user, "id_pelatihan"=>$id_pelatihan), "cdate", "ASC", " transactional_binaan")->result();
          foreach ($binaan as $b) {
            $no_kk = $b->nomor_kk;
            $daftar = $this->GeneralApiModel->getWhereTransactional(array("nomor_kk"=>$no_kk), 'user_anggotakeluarga_detail')->result()[0];
            array_push($list_binaan, array("id"=>$b->id,"nomor_kk"=>$no_kk, "nama"=>$daftar->namalengkap));
          }

          $kader = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_pembina"=>$id_user, "id_pelatihan"=>$id_pelatihan, "role"=>1), "cdate", "ASC", " transactional_kode_referal")->result();
          foreach ($kader as $b) {
            $id_user = $b->id_user;
            $daftar = $this->GeneralApiModel->getWhereTransactional(array("id"=>$id_user), 'transactional_user')->result()[0];
            array_push($list_kader, array("id_user"=>$id_user, "nama"=>$daftar->namalengkap));
          }
        }
        $result = array(
          'jml_keluarga_binaan'=>count($list_binaan),
          'jml_kader_binaan'=>count($list_kader),
          'list_keluarga_binaan'=>$list_binaan,
          'list_kader_binaan'=>$list_kader,
        );
        $this->response(array('status' => 200, 'message' => 'Data berhasil didapatkan', 'data' => $result));
      } else {
        $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan, id user / id pelatihan tidak tersedia!', 'data' => null));
      }
    }

    function lihatDaftarBinaan_post(){
      $id_user = $this->input->post('id_user');
      $id_kelas = $this->input->post('id_kelas');
      if(!empty($id_kelas) && !empty($id_user)){
        $kelas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id"=>$id_kelas), "cdate", "DESC", "transactional_kelas")->result();
        $list_binaan = array();
        $list_kader = array();
        if ($kelas) {
          $kelas = $kelas[0];
          $id_pelatihan = $kelas->id_pelatihan;
          $binaan = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_pembina"=>$id_user, "id_pelatihan"=>$id_pelatihan), "cdate", "ASC", " transactional_binaan")->result();
          foreach ($binaan as $b) {
            $no_kk = $b->nomor_kk;
            $daftar = $this->GeneralApiModel->getWhereTransactional(array("nomor_kk"=>$no_kk), 'user_anggotakeluarga_detail')->result()[0];
            $anggota = $this->GeneralApiModel->getWhereTransactional(array("nomor_kk"=>$no_kk), 'user_anggotakeluarga_detail')->result();
            array_push($list_binaan, array(
              "jml_anggota"=>count($anggota),
              "nama"=>$daftar->namalengkap,
              "nama_kota"=> $daftar->nama_kota,
              "nama_provinsi"=> $daftar->nama_provinsi,
              "status"=> $b->status_acc
            ));
          }

          $kader = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_pembina"=>$id_user, "id_pelatihan"=>$id_pelatihan, "role"=>1), "cdate", "ASC", " transactional_kode_referal")->result();
          foreach ($kader as $b) {
            $id_user = $b->id_user;
            $daftar = $this->GeneralApiModel->getWhereTransactional(array("id"=>$id_user), 'transactional_user')->result()[0];
            $anggota = $this->GeneralApiModel->getWhereTransactional(array("nomor_kk"=>$no_kk), 'keluargabinaan_detail')->result();
            array_push($list_kader, array(
              "jml_anggota"=>count($anggota),
              "nama"=>$daftar->namalengkap,
              "nama_kota"=> $anggota[0]->nama_kota,
              "nama_provinsi"=> $anggota[0]->nama_provinsi,
              "status"=> $b->status_acc
            ));
          }
        }

        $result = array(
          'data_kader_binaan'=>$list_kader,
          'data_keluarga_binaan'=>$list_binaan
        );
        $this->response(array('status' => 200, 'message' => 'Data berhasil didapatkan', 'data' => $result));
      } else {
        $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan, id user / id pelatihan tidak tersedia!', 'data' => null));
      }
    }

    function date_diff($date){
      $now = new DateTime();
      $now = date_format($now, 'Y-m-d');

      $diff = abs(strtotime($now) - strtotime($date));
      $hari = (strtotime($now) - strtotime($date))/60/60/24;

      return intval($hari);
    }

    function get_umur($date){
      $dob = new DateTime($date);
      $now = new DateTime();

      $datetime1 = date_create($dob->format('Y-m-d'));
      $datetime2 = date_create($now->format('Y-m-d'));

      $interval = date_diff($datetime1, $datetime2);

      $year = $interval->format('%y');

      return $year;
    }
  }
