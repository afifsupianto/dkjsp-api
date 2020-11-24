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
      $this->load->model("AktivitasApiModel");
      $this->load->model("SkriningApiModel");
      $this->load->library('Pdf');
    }

    function dataAktivitas_post(){
      $time_expired=60*60*24*3;
      $time_aweek=$time_expired*2;
      header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
      $user = array(
        'id' => $this->input->post('id_user')
      );

      $id_grading = $this->input->post('id_grading');
      // $id_grading = 1;

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

          $this->response(array('status' => 200, 'message' => 'Sukses', 'data' => ($list_aktivitas?$list_aktivitas[0]:null)));
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

      if(!empty($id_pelatihan) && !empty($id_user) && !empty($id_kelas)){
        $semua_kondisi = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_hasil_skrining")->result();
        $kondisi = ($semua_kondisi?$semua_kondisi[0]:null);
        $semua_presensi = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_presensi")->result();
        $presensi = ($semua_presensi?$semua_presensi[0]:null);

        // $kelas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id"=>$id_kelas), "cdate", "DESC", "transactional_kelas")->result()[0];
        // $id_pelatihan = $kelas->id_pelatihan;
        $semua_aktivitas = $this->AktivitasApiModel->getAktivitasHarian(array("id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan))->result();
        $aktivitas = ($semua_aktivitas?$semua_aktivitas[0]:null);

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
        'presensi_terakhir'=>$presensi->cdate,
        'aktivitas_harian_terakhir'=>$aktivitas->cdate,
        'total_aktivitas_harian'=>count($semua_aktivitas),
        'is_aktivitas_harian'=>($aktivitas?($this->date_diff($aktivitas->cdate)==0?true:false):null),
        'kondisi_fisik' => ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_fisik),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
        'kondisi_mental' => ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_mental),'masterdata_grading_status_kesehatan')->result()[0]->nama:null),
        'id_grading'=>($kondisi?$kondisi->kondisi_fisik:null),
        'countdown_next'=>$this->countdown_next($kondisi->cdate),
        'list_skrining'=>$list_skrining
      );

      $this->response(array('status' => 200, 'message' => 'Data aktivitas peserta berhasil didapatkan', 'data' => $result));
    } else {
      $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan, id user / id kelas / id pelatihan salah!', 'data' => null));
    }
  }

  function myAktivitas_post(){
    $time_expired=60*60*24*3;
    $time_aweek=$time_expired*2;
    header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
    $id_user = $this->input->post('id_user');
    $id_kelas = $this->input->post('id_kelas');
    $id_pelatihan = $this->input->post('id_pelatihan');

    if(!empty($id_pelatihan) && !empty($id_user) && !empty($id_kelas)){
      $semua_presensi = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user, "id_kelas"=>$id_kelas, "id_pelatihan"=>$id_pelatihan), "cdate", "DESC", "transactional_presensi")->result();
      $semua_materi = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user,"status_progress"=>1,"id_kelas"=>$id_kelas), "cdate", "DESC", "list_subbab_progress_cdate")->result();
      // $semua_test = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_test")->result();

      $gabung = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_kode_referal")->result();
      $gabung = ($gabung?$gabung[0]:null);

      $semua_presensi = $this->GeneralApiModel->getWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_presensi")->result();
      $presensi = ($semua_presensi?$semua_presensi:null);

      $semua_aktivitas = $this->AktivitasApiModel->getAktivitasHarian(array("id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan))->result();
      $aktivitas = ($semua_aktivitas?$semua_aktivitas[0]:null);

      $semua_kondisi = $this->SkriningApiModel->getAllSkrining(array("id_user"=>$id_user), "cdate", "DESC", "transactional_hasil_skrining")->result();
      $list_kondisi = array();
      foreach ($semua_kondisi as $p) {
        array_push($list_kondisi, array('tipe'=>4, 'time'=>strtotime($p->cdate)*1000, 'data'=>'Sudah melakukan skrining'));
      }

      $list_presensi = array();
      foreach ($presensi as $p) {
        array_push($list_presensi, array('tipe'=>0, 'time'=>strtotime($p->cdate)*1000, 'data'=>'Sudah mengisi presensi harian'));
      }

      $list_laporan = array();
      foreach ($semua_aktivitas as $p) {
        array_push($list_laporan, array('tipe'=>1, 'time'=>strtotime($p->cdate)*1000, 'data'=>'Sudah mengisi laporan harian'));
      }

      $list_materi = array();
      foreach ($semua_materi as $p) {
        $judul = $this->GeneralApiModel->getWhereMaster(array('id'=>$p->id_materi), 'masterdata_materi')->result()[0]->judul;
        if($p->is_test == 1 ){
          array_push($list_materi, array('tipe'=>3, 'time'=>strtotime($p->cdate)*1000, 'data'=>"Sudah menyelesaikan materi $judul - ($p->judul_subbab)"));
        }else{
          array_push($list_materi, array('tipe'=>2, 'time'=>strtotime($p->cdate)*1000, 'data'=>"Sudah menyelesaikan materi $judul - ($p->judul_subbab)"));
        }
      }

      //   $list_test = array();
      //   foreach ($semua_test as $p) {
      //     $judul = $this->GeneralApiModel->getWhereMaster(array('id'=>$p->id_materi), 'masterdata_materi')->result()[0]->judul;
      //     $subbab = $this->GeneralApiModel->getWhereMaster(array('id'=>$p->id_subbab_materi), 'masterdata_subbab_materi')->result()[0]->judul;
      //     array_push($list_test, array('tipe'=>3, 'time'=>strtotime($p->cdate)*1000, 'data'=>"Sudah menyelesaikan materi $judul - ($subbab)"));
      //   }


      $list_aktivitas = array_merge($list_presensi, $list_laporan, $list_materi,$list_kondisi);

      $result = array(
        'total_absent'=>$this->date_diff($gabung->tgl_join)-count($semua_presensi),
        // 'total_absent'=>$this->date_diff($gabung->tgl_join),
        'total_presensi'=>count($semua_presensi),
        'total_hari'=>$this->date_diff($gabung->tgl_join),
        'isi_laporan'=>($aktivitas?($this->date_diff($aktivitas->cdate)==0?true:false):null),
        'list_aktivitas'=>$list_aktivitas
      );
      $this->response(array('status' => 200, 'message' => 'Data berhasil didapatkan', 'data' => $result));
    } else {
      $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan, id user / id kelas / id pelatihan salah!', 'data' => null));
    }
  }

  function detailLaporan_post(){
    $time_expired=60*60*24*3;
    $time_aweek=$time_expired*2;
    header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
    $id_user = $this->input->post('id_user');
    $id_kelas = $this->input->post('id_kelas');
    $id_pelatihan = $this->input->post('id_pelatihan');
    $id_pembina = $this->input->post('id_pembina');
    $tgl_aktivitas = $this->input->post('tgl_aktivitas');

    if(!empty($id_pelatihan) && !empty($id_user) && !empty($id_kelas) && !empty($id_pembina) && !empty($tgl_aktivitas) ){
      $list_aktivitas = array();
      $list_soal = array();
      $list_jawaban = array();

      $sdate = "$tgl_aktivitas 00:00:00";
      $edate = "$tgl_aktivitas 23:59:59";

      $semua_aktivitas = $this->AktivitasApiModel->getAktivitasPerSoal(array("id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan, 'cdate >='=>$sdate, 'cdate <='=>$edate))->result();

      foreach ($semua_aktivitas as $kd => $vd) {
        $soal = $this->GeneralApiModel->getWhereMaster(array('id'=>$vd->id_soal), "masterdata_soal_aktivitas")->result();
        foreach ($soal as $ks => $vs) {
          $jawaban = $this->GeneralApiModel->getWhereTransactional(array('id_soal'=>$vs->id, "id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan, 'cdate >='=>$sdate, 'cdate <='=>$edate), "transactional_hasil_aktivitas")->result();
          foreach ($jawaban as $kj => $vj) {
            $nama = $this->GeneralApiModel->getWhereMaster(array('id'=>$vj->id_jawaban), "masterdata_pilihan_jawaban_aktivitas")->result();
            array_push($list_jawaban, array("tipe"=>$vs->tipe, "nilai"=>($vj->nilai==1?true:false), "jawaban"=>$nama[0]->jawaban));
          }
          $topik = $this->GeneralApiModel->getWhereMaster(array('id'=>$vs->id_topik), 'masterdata_topik')->result();
          array_push($list_soal, array("soal"=>$vs->soal, "id_topik"=>$topik[0]->id, "topik"=>$topik[0]->nama,"data_jawaban"=>$list_jawaban));
          $list_jawaban = array();
        }
        array_push($list_aktivitas, $list_soal[0]);
        $list_soal = array();
      }

      $p_aktivitas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan),'cdate','DESC','transactional_hasil_aktivitas')->result();
  		$p_aktivitas = ($p_aktivitas?$p_aktivitas[0]:null);

  		$p_grading = $this->GeneralApiModel->getWhereMaster(array("id"=>$p_aktivitas->id_aktivitas), 'masterdata_aktivitas')->result();
  		$p_grading = ($p_grading?$p_grading[0]:null);

      $p_user = $this->GeneralApiModel->getWhereTransactional(array("id"=>$id_user), 'transactional_user')->result();
  		$p_user = ($p_user?$p_user[0]:null);

  		$p_pembina = $this->GeneralApiModel->getWhereTransactional(array("id"=>$id_pembina), 'transactional_user')->result();
  		$p_pembina = ($p_pembina?$p_pembina[0]:null);

      $html  = '<h3 style="text-align:center;">REPORT AKTIVITAS HARIAN</h3>';
      $html .= '<h5 style="text-align:center; text-transform:capitalize;">Nama Aktivitas : Aktivitas '.($p_grading?$p_grading->nama:null).'</h5>';
      $html .= '<h5 style="text-align:center; text-transform:capitalize;">Nama Keluarga : '.($p_user?$p_user->namalengkap:null).'</h5>';
      $html .= '<h5 style="text-align:center; text-transform:capitalize;">Nama Pembina : '.($p_pembina?$p_pembina->namalengkap:null).'</h5>';
      $html .= '<h5 style="text-align:center;">Tanggal : '.$tgl_aktivitas.'</h5>';
      $html .= '<h4 style=""></h4>';
      $html .= '<h4 style=""></h4>';

      $i = 1;
      $id_topik_temp = 0;
      foreach ($semua_aktivitas as $kd => $vd) {
        $soal = $this->GeneralApiModel->getWhereMaster(array('id'=>$vd->id_soal), "masterdata_soal_aktivitas")->result();
        foreach ($soal as $ks => $vs) {
          $p_topik = $this->GeneralApiModel->getWhereMaster(array("id"=>$vs->id_topik), 'masterdata_topik')->result();
          $id_topik = $p_topik[0]->id;
          if ($id_topik!=$id_topik_temp) {
            $html .= '<h4 style="">'.$i++.'. '.$p_topik[0]->nama.'</h4>';
          } else {
            $html .= '<h4 style=""></h4>';
          }
          $html .= '<label style="text-decoration:underline; border-bottom:3px solid black;"><b><u>'.$vs->soal.'</u></b></label><br/>';
          $id_topik_temp = $id_topik;
          $jawaban = $this->GeneralApiModel->getWhereTransactional(array('id_soal'=>$vs->id, "id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan, 'cdate >='=>$sdate, 'cdate <='=>$edate), "transactional_hasil_aktivitas")->result();
          foreach ($jawaban as $kj => $vj) {
            $nama = $this->GeneralApiModel->getWhereMaster(array('id'=>$vj->id_jawaban), "masterdata_pilihan_jawaban_aktivitas")->result();
            $html .= '<table><tr>';
            if ($vs->tipe==0) {
              $detail_jawaban = '<td>'.$nama[0]->jawaban.'</td><td><b>('.($vj->nilai==1?'Ya':'Tidak').')</b></td><td></td>';
            } else {
              if ($vj->nilai==1) {
                $detail_jawaban = '<td>'.$nama[0]->jawaban.'</td><td></td>';
              } else {
                continue;
              }
            }
            $html .= $detail_jawaban;
            $html .= '</tr></table>';
          }
        }
      }
      $cetak = $this->cetak($html, $tgl_aktivitas);
      $str = "Content-Type: application/pdf;\r\n name=\"Laporan_Harian_$tgl_aktivitas.pdf\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment;\r\n filename=\"Laporan_Harian_$tgl_aktivitas.pdf\"\r\n\r\n";
      $pdf = str_replace($str, '', $cetak);

      $result = array(
        'file_pdf'=>$pdf,        
        'data_record'=>$list_aktivitas
      );

      $this->response(array('status' => 200, 'message' => 'Data berhasil didapatkan', 'data' => $result));
    } else {
      $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan, id user / id kelas / id pelatihan / id pembina / tanggal salah!', 'data' => null));
    }
  }

  function countdown_next($date){
    $now = new DateTime();
    $now = date_format($now, 'Y-m-d H:m:s');

    $diff = abs(strtotime($now) - strtotime($date));
    // $hari = (strtotime($now) - strtotime($date))/60/60/24;
    $waktu = (strtotime($now) - strtotime($date))/60/60+1;

    // return intval($hari);
    $hari = (intval($waktu/24)+13);
    $jam = 24-intval($waktu%24);
    return array('hari'=>$jam==24?$hari+1:$hari, 'jam'=>$jam==24?0:$jam);
  }

  function date_diff($date){
    $now = new DateTime();
    $now = date_format($now, 'Y-m-d h:m:s');

    $diff = abs(strtotime($now) - strtotime($date));
    $hari = (strtotime($now) - strtotime($date))/60/60/24;

    return intval($hari);
  }

  function cetak($html, $tgl){
    $pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetTitle('File');
    $pdf->SetMargins(20,20,20,20);
    // $pdf->SetTopMargin(20);
    $pdf->setFooterMargin(20);
    $pdf->SetAutoPageBreak(true);
    $pdf->SetAuthor('DKJPS');
    $pdf->SetDisplayMode('real', 'default');
    $pdf->AddPage();

    $pdf->writeHTML($html, true, false, true, false, '');
    return $pdf->Output('Laporan Harian '.$tgl.'.pdf', 'E');
  }
}
