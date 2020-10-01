<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class KelasApiController extends REST_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        date_default_timezone_set("Asia/Jakarta"); 
        $this->dateToday = date("Y-m-d");
        $this->timeToday = date("h:i:s");
        $this->load->model("GeneralApiModel");
    }

    function gabungKelas_post(){
        $random = random_string('alnum', 6);
        $data = array(
            'kode_referal' => $random,
            'role' => 0,
            'status_pembina' => 0,
            'id_user' => $this->input->post('id_user'),
            'id_kelas' => $this->input->post('id_kelas'),
            'id_pelatihan' => $this->input->post('id_pelatihan'),
            'tgl_join' => $this->dateToday,
            'statusdata' => 0
        );

        $exist_check = array(
            'id_kelas' => $data['id_kelas'],
            'status_kelas !=' => 1
        );

        $exist = $this->GeneralApiModel->isDataTransactionalExist($exist_check, "kelas_pelatihan");

        if(!$exist){
            $exist_check = array(
                'id_user' => $data['id_user'],
                'id_kelas' => $data['id_kelas'],
                'id_pelatihan' => $data['id_pelatihan']
            );
    
            $exist = $this->GeneralApiModel->isDataTransactionalExist($exist_check, "transactional_kode_referal");
    
            if(!$exist){
                $insert = $this->GeneralApiModel->insertTransactional($data, "transactional_kode_referal");
                $this->response(array('status' => 200, 'message' => 'Gabung Kelas Berhasil!', 'data' => $insert));
            }
            else{
                $this->response(array('status' => 200, 'message' => 'Anda telah bergabung pada kelas ini!', 'data' => false));
            }
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Kelas belum dibuka atau sedang berlangsung!', 'data' => false));
        }
    }

    function keluarKelas_post(){
        $data = array(
            'id_user' => $this->input->post('id_user'),
            'id_kelas' => $this->input->post('id_kelas')
        );

        $exist_check = array(
            'id_kelas' => $data['id_kelas'],
            'status_kelas >' => 1 //0 = belum dibuka, 1 = pendaftaran, 2 = berjalan, 3 = selesai
        );
        $exist = $this->GeneralApiModel->isDataTransactionalExist($exist_check, "kelas_pelatihan");
        if(!$exist){
            $delete = $this->GeneralApiModel->deleteTransactional($data, "transactional_kode_referal");
            $this->response(array('status' => 200, 'message' => 'Anda berhasil keluar dari kelas!', 'data' => $delete));
        }else{
            $this->response(array('status' => 200, 'message' => 'Tidak dapat keluar kelas! Kelas sedang berlangsung atau telah selesai', 'data' => false));
        }
    }

    function cariKelas_post(){
        $data = array(
            'kode_referal' => $this->input->post('kode_referal')
        );
        if(!empty($data)){
            $result = $this->GeneralApiModel->getWhereTransactional($data, "kelas_pelatihan")->row();
            if(!empty($result)){
                $this->response(array('status' => 200, 'message' => 'Pencarian Kelas Berhasil!', 'data' => $result));
            }else{
                $this->response(array('status' => 200, 'message' => 'kelas dengan kode referal '.$data['kode_referal'].' tidak ditemukan!', 'data' => null));
            }
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Kode Referal kosong! data tidak ditemukan', 'data' => null));
        }
    }

    function masukkanKelas_post(){
        $random = random_string('alnum', 6);
        $data = array(
            'nama' => $this->input->post('nama'),
            'kapasitas' => $this->input->post('kapasitas'),
            'is_buka_pendaftaran' => 0,
            'tgl_buka' => $this->input->post('tgl_buka'),
            'tgl_selesai' => $this->input->post('tgl_selesai'),
            'id_pelatihan' => $this->input->post('id_pelatihan'),
            'kode_referal' => $random
        );
        $exist_check = array(
            'nama' => $data['nama'],
            'id_pelatihan' => $data['id_pelatihan']
        );
        $nama_exist = $this->GeneralApiModel->isDataTransactionalExist($exist_check, "transactional_kelas");
        if($nama_exist){
            $this->response(array('status' => 200, 'message' => 'Nama Kelas sudah tersedia!', 'data' => null));
        }
        else{
            $insert = $this->GeneralApiModel->insertTransactional($data, "transactional_kelas");
            $this->response(array('status' => 200, 'message' => 'Input  kelas sukses!', 'data' => $insert));
        }
    }
    
    function submitPresensi_post(){
        $data = array(
            'id_pelatihan' => $this->input->post('id_pelatihan'),
            'id_user' => $this->input->post('id_user'),
            'id_kelas' => $this->input->post('id_kelas')
        );
        if($data['id_pelatihan'] && $data['id_user'] && $data['id_kelas']){
            $insert = $this->GeneralApiModel->insertTransactional($data, "transactional_presensi");
            $this->response(array('status' => 200, 'message' => 'Input presensi sukses!', 'data' => $insert));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Form presensi tidak lengkap! submit presensi gagal', 'data' => null));
        }
    }
    
    function submitTopikSelesai_post(){
        $data = array(
            'id_materi' => $this->input->post('id_materi'),
            'id_subbab_materi' => $this->input->post('id_subbab_materi'), 
            'id_user' => $this->input->post('id_user')
        );
        if($data['id_materi'] && $data['id_subbab_materi'] && $data['id_user']){
            $insert = $this->GeneralApiModel->insertTransactional($data, "transactional_progress_materi");
            $this->response(array('status' => 200, 'message' => 'Input progress materi sukses!', 'data' => $insert));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Form input progress tidak lengkap! submit progress materi gagal', 'data' => null));
        }
    }
    
    function submitTest_post(){
        $data_jawaban = $this->post('data_jawaban');
        $id_subbab_materi = $this->post('id_subbab_materi');
        $id_user = $this->post('id_user');
        $data = array(
            'data_jawaban' => $data_jawaban,
            'id_subbab_materi' => $id_subbab_materi,
            'id_user' => $id_user
        );
        if(!empty($data_jawaban) && !empty($id_subbab_materi) && !empty($id_user)){
            $query = $this->GeneralApiModel->getWhereTransactional(array('id_subbab_materi' => $id_subbab_materi),"counting_jumlah_soal_test")->row();
            $jumlah_soal = $query->jumlah_soal;
            if(!empty($jumlah_soal)){
                $jumlah_benar = 0;
                for($i = 0; $i < $jumlah_soal; $i++){
                    if(!empty($data_jawaban[$i])){
                        $jawaban = array(
                            'id' => $data_jawaban[$i]['id_jawaban']
                        );
                        $kunci_jawaban = $this->GeneralApiModel->getWhereMaster($jawaban, "masterdata_pilihan_jawaban_test")->row();
                        if(!empty($kunci_jawaban)){
                            if($kunci_jawaban->is_benar == 1){
                                $jumlah_benar += 1;
                            }
                        }
                    }
                };
                $id_materi = $this->GeneralApiModel->getWhereMaster(array('id' => $data['id_subbab_materi']),"masterdata_subbab_materi")->row();
                $progress = array(
                    'id_user' => $data['id_user'],
                    'id_materi' => $id_materi->id_materi,
                    'id_subbab_materi' => $data['id_subbab_materi']
                );
                
                $hasil_test = array(
                    'jml_soal' => $jumlah_soal,
                    'jml_benar' => $jumlah_benar
                );
                
                $data_exist = $this->GeneralApiModel->isDataTransactionalExist($progress, "transactional_progress_materi");
                if($data_exist){
                    $this->response(array('status' => 200, 'message' => 'test sudah dilakukan!', 'data' => null));
                }
                else{
                    $insert = $this->GeneralApiModel->insertTransactional($progress, "transactional_progress_materi");
                    
                    $log_test = array(
                        'tanggal' => $this->dateToday." ".$this->timeToday,
                        'jumlah_soal' => $hasil_test['jml_soal'],
                        'jumlah_benar' => $hasil_test['jml_benar'],
                        'id_user' => $progress['id_user'],
                        'id_materi' => $progress['id_materi'],
                        'id_subbab_materi' => $progress['id_subbab_materi']
                    );
                    $insert_log_test = $this->GeneralApiModel->insertIdTransactional($log_test, "transactional_test");
                    
                    for($i = 0; $i < count($data_jawaban); $i++){
                        $data_jawaban[$i]['id_trans_test'] = $insert_log_test;
                    }
                    $insert_data_jawaban = $this->GeneralApiModel->insertBatchTransactional($data_jawaban, "transactional_list_jawaban_test");
                    
                    $this->response(array('status' => 200, 'message' => 'Input  test sukses!', 'data' => $hasil_test));
                }
                
            }else{
                $this->response(array('status' => 200, 'message' => 'id subbab tidak ditemukan!', 'data' => null));
            }
        }else{
            $this->response(array('status' => 200, 'message' => 'submit test gagal dilakukan!', 'data' => null));
        }
        //$this->response(array('status' => 200, 'message' => 'test', 'data' => $data_jawaban));
    }
    
    function dataKodeReferal_post(){
        $data = array(
            'id_pelatihan' => $this->input->post('id_pelatihan'),
            'id_user' => $this->input->post('id_user'),
            'id_kelas' => $this->input->post('id_kelas')
        );
        if($data['id_pelatihan'] && $data['id_user'] && $data['id_kelas']){
            $result = $this->GeneralApiModel->getWhereTransactional($data, "transactional_kode_referal")->row();
            $this->response(array('status' => 200, 'message' => 'Kode referal berhasil didapatkan', 'data' => $result->kode_referal));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'id yang diperlukan tidak lengkap! data gagal ditampilkan', 'data' => null));
        }
    }
    
    function perbaruiReferal_post(){
        $kode_referal = random_string('alnum', 6);
        
        $id_user = $this->input->post('id_user');
        $where = array(
            'id_user' => $id_user,
            'id_pelatihan' => $this->input->post('id_pelatihan'),
            'id_kelas' => $this->input->post('id_kelas')
        );

        $data = array(
            'kode_referal' => $kode_referal
        );

        $result = $this->GeneralApiModel->updateTransactional($data,$where,"transactional_kode_referal");

        if($result){
            $this->response(array('status' => 200, 'message' => 'Kode Referal Berhasil Diperbarui', 'data' => $data['kode_referal']));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Peserta tidak ditemukan! Kode Referal Gagal Diperbarui', 'data' => null));
        }
    }
    
    function dataTest_post(){
        $where = array(
            'id_subbab_materi' => $this->input->post('id_subbab_materi')
        );
        if($where['id_subbab_materi']){
            $data = array(
                'id_subbab_materi' => $where['id_subbab_materi'],
                'data_soal' => array()
            );
            $result = $this->GeneralApiModel->getWhereMaster($where, "masterdata_test")->result();
            $i = 0;
            if(!empty($result)){
                foreach($result as $row){
                    $data['data_soal'][$i]['id_soal'] = $row->id;
                    $data['data_soal'][$i]['soal'] = $row->soal;
                    $data['data_soal'][$i]['tipe'] = $row->tipe;
                    $data['data_soal'][$i]['id_subbab_materi'] = $row->id_subbab_materi;
                    $data['data_soal'][$i]['data_jawaban'] = array();
                    
                    $where2 = array(
                        'id_test' => $row->id
                    );
                    
                    $result2 = $this->GeneralApiModel->getWhereMaster($where2, "masterdata_pilihan_jawaban_test")->result();
                    $j = 0;
                    foreach($result2 as $row){
                        $data['data_soal'][$i]['data_jawaban'][$j]['id'] = $row->id;
                        $data['data_soal'][$i]['data_jawaban'][$j]['jawaban'] = $row->jawaban;
                        $data['data_soal'][$i]['data_jawaban'][$j]['is_benar'] = $row->is_benar;
                        
                        $j++;
                    }
                    
                    $i++;
                }
                $this->response(array('status' => 200, 'message' => 'Data test berhasil didapatkan!', 'data' => $data));
            }else{
                $this->response(array('status' => 200, 'message' => 'Subbab materi tidak ditemukan!', 'data' => null));
            }
        }else{
             $this->response(array('status' => 200, 'message' => 'Masukkan id subbab materi terlebih dahulu! data tidak ditemukan', 'data' => null));
        }
    }
}