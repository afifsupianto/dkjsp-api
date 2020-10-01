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
        $this->load->model("PesertaApiModel");
        $this->load->model("GeneralApiModel");
    }

    function homePeserta_post(){
        $time_expired=60*60*24*3;
        $time_aweek=$time_expired*2;
        header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
        $id_kelas = array(
            'id_kelas' => $this->input->post('id_kelas'),
            'id_user' => $this->input->post('id_user')
        );
        if(!empty($id_kelas['id_kelas']) && !empty($id_kelas['id_user'])){
            $data = array(
                'kondisi_fisik' => 'sehat',
                'kondisi_mental' => 'sehat',
                'skrining_terakhir'=> array(
                        'id'=>1,
                        'is_sudah_skrining'=>true
                    ),
                'presensi_terakhir' => array(
                        'waktu'=>'27/06/2020',
                        'is_sudah_presensi'=>true
                    ),
                'laporan_harian_terakhir' => array(
                        'id'=> 1,
                        'waktu'=>'27/06/2020',
                        'is_sudah_isi_laporan'=>true
                    ),
                'list_materi' => array(),
                'list_keluargabinaan' => array(),
                'list_kader' => array()
            );
            $result = $this->GeneralApiModel->getWhereTransactionalOrdered($id_kelas,"id_materi","ASC","list_materi_jadwal")->result();
            $i = 0;
            //foreach untuk set daftar materi
            foreach($result as $row){
                $data['list_materi'][$i]['id'] = $row->id_materi;
                $data['list_materi'][$i]['judul_materi'] = $row->judul_materi;
                $data['list_materi'][$i]['jumlah_subbab'] = $row->jml_subbab;
                $data['list_materi'][$i]['jumlah_subbab_selesai'] = $row->jml_subbab_selesai;
                if($row->status_buka == 1){
                    if(($row->jml_subbab == $row->jml_subbab_selesai) && $row->jml_subbab != 0){
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

            $this->response(array('status' => 200, 'message' => 'Data home peserta berhasil didapatkan', 'data' => $data));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Data tidak ditemukan!', 'data' => null));
        }
    }

    function menuMateri_post(){
        $time_expired=60*60*24*3;
        $time_aweek=$time_expired*2;
        header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
        $id_kelas = array(
            'id_kelas' => $this->input->post('id_kelas'),
            'id_user' => $this->input->post('id_user')
        );
        if(!empty($id_kelas['id_kelas']) && !empty($id_kelas['id_user'])){
            $data = array();
            $result = $this->GeneralApiModel->getWhereTransactional(array("id_kelas" => $this->input->post('id_kelas')), "kelas_pelatihan")->row();
            if(!empty($result)){
                $data['deskripsi_pelatihan'] = $result->deskripsi_pelatihan;
                $data['jml_materi_dipelajari'] = "3";
                $data['judul_materi_terakhir'] = "COVID-19";
                $data['jumlah_anggota_kelas'] = $result->jumlah_peserta; //buat view counting di kode referal where role = 0
                $data['nama_kelas'] = $result->nama_kelas;
                $data['nilai_rata_seluruh_test'] = "76";
                $tgl_buka = date("d-m-Y", strtotime($result->tgl_buka));
                $tgl_selesai = date("d-m-Y", strtotime($result->tgl_selesai));
                $data['periode_kelas'] = strval($tgl_buka)." - ".strval($tgl_selesai); //tgl buka - tgl selesai
                $data['status_kelas'] = "1";
                        $data['list_riwayat_test']=array();
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
                        if(($row->jml_subbab == $row->jml_subbab_selesai) && $row->jml_subbab != 0){
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

}
