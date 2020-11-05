<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class KeluargaBinaanApiController extends REST_Controller
{

    function __construct($config = 'rest')
    {
        parent::__construct($config);
        date_default_timezone_set("Asia/Jakarta");
        $this->dateToday = date("Y-m-d H:i:s");
        $this->timeToday = date("h:i:s");
        $this->load->model("GeneralApiModel");
        $this->load->model("KeluargaBinaanApiModel");
    }

    function getDataKeluargaBinaan_post(){
        $where = array(
            'nomor_kk' => $this->input->post('nomor_kk')
        );
        $result = $this->GeneralApiModel->getWhereTransactional($where, 'keluargabinaan_detail')->result();
        if(!empty($result)){
            $this->response(array('status' => 200, 'message' => 'Data Berhasil Ditemukan!', 'data' => $result[0]));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Data Keluarga Binaan Tidak Ditemukan!', 'data' => null));
        }
    }

    function detailKeluarga_post(){
        $data = array(
            'no_kk' => $this->input->post('no_kk')
        );
        $id_user = $this->input->post('id_user');
        $id_kelas = $this->input->post('id_kelas');
        $id_pelatihan = $this->input->post('id_pelatihan');
        if(!empty($data['no_kk']) && !empty($id_kelas) && !empty($id_pelatihan)){
            $check_kepala_keluarga = array(
                'nomor_kk' => $data['no_kk'],
                'status_keluarga' => 0
            );

            $data_kepala_keluarga = $this->GeneralApiModel->getWhereTransactional($check_kepala_keluarga, "transactional_anggota_keluarga")->row();
            if(!empty($data_kepala_keluarga)){
                $response = array();
                $data_alamat = $this->GeneralApiModel->getWhereTransactional(array('nomor_kk' => $data['no_kk']), "keluargabinaan_detail")->row();
                $data_user = $this->GeneralApiModel->getWhereTransactional(array('id' => $data_kepala_keluarga->id_user), "transactional_user")->row();

                $response['nama_kepel'] = $data_user->namalengkap;
                if(!empty($data_alamat)){
                    $response['alamat'] = $data_alamat->alamat_lengkap;
                    $response['nama_kota'] = $data_alamat->nama_kota;
                    $response['nama_provinsi'] = $data_alamat->nama_provinsi;
                }else{
                    $response['alamat'] = "tidak ada";
                    $response['nama_kota'] = "tidak ada";
                    $response['nama_provinsi'] = "tidak ada";
                }
                $response['no_kk'] = $data_kepala_keluarga->nomor_kk;
                $response['nohp_kepel'] = $data_user->nohp;

                $data_kader = $this->GeneralApiModel->getWhereTransactional(array('role'=>1, 'id_user'=>$data_kepala_keluarga->id_user), "transactional_kode_referal")->row();
                if(!empty($data_kader)){
                    $response['status_acc_relawan'] = $data_kader->status_acc;
                    $response['tgl_bergabung'] = $data_kader->tgl_join;
                }else{
                    $response['status_acc_relawan'] = 0;
                    $response['tgl_bergabung'] = '0000-00-00';
                }

                $data_anggota_keluarga = $this->GeneralApiModel->getWhereTransactional(array('nomor_kk' => $data['no_kk']), "transactional_anggota_keluarga")->result();
                $i = 0;
                $list_anggota = array();
                $list_status_keluarga = array("Kepala Keluarga", "Istri", "Anak");
                foreach($data_anggota_keluarga as $row){

                    $data_anggota = $this->GeneralApiModel->getWhereTransactional(array('id' => $row->id), "transactional_user")->row();

                    $kondisi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$row->id), "cdate", "DESC", "transactional_hasil_skrining")->result();
                    $kondisi = ($kondisi?$kondisi[0]:null);
                    $kondisi_fisik = ($kondisi?$this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_fisik),'masterdata_grading_status_kesehatan')->result()[0]->nama:null);
                    $kondisi_mental = ($kondisi?$this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_mental),'masterdata_grading_status_kesehatan')->result()[0]->nama:null);

                    $presensi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$row->id), "cdate", "DESC", "transactional_presensi")->result();
                    $presensi = ($presensi?$presensi[0]:null);

                    $kelas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id"=>$id_kelas), "cdate", "DESC", "transactional_kelas")->result()[0];
                    $id_pelatihan = $kelas->id_pelatihan;
                    $aktivitas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$row->id, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan), "cdate", "DESC", "transactional_hasil_aktivitas")->result();
                    $aktivitas = ($aktivitas?$aktivitas[0]:null);

                    array_push($list_anggota, array(
                      "id_user"=> $row->id_user,
                      "kondisi_fisik"=> $kondisi_fisik,
                      "kondisi_mental"=> $kondisi_mental,
                      "nama"=> "$data_anggota->namalengkap",
                      "nik"=> $row->nik_anggota,
                      "terakhir_isi_aktivitas"=> ($aktivitas?$aktivitas->cdate:null),
                      "terakhir_presensi"=> ($presensi?$presensi->cdate:null),
                      "umur"=> floor((time() - strtotime($data_anggota->tgl_lahir)) / 31556926),
                      "status_keluarga"=> $list_status_keluarga[$row->status_keluarga]
                    ));
                    //
                    $response['anggota_keluarga'] = $list_anggota;
                    // $response['anggota_keluarga'][$i]['kondisi_mental'] = $kondisi_mental;
                    // $response['anggota_keluarga'][$i]['nama'] = $data_anggota->namalengkap;
                    // $response['anggota_keluarga'][$i]['nik'] = $row->nik_anggota;
                    // $response['anggota_keluarga'][$i]['terakhir_isi_aktivitas'] = ($aktivitas?$aktivitas->cdate:null);
                    // $response['anggota_keluarga'][$i]['terakhir_presensi'] = ($presensi?$presensi->cdate:null);
                    // $response['anggota_keluarga'][$i]['umur'] = floor((time() - strtotime($data_anggota->tgl_lahir)) / 31556926);
                }
                $this->response(array('status' => 200, 'message' => 'test', 'data' => $response));
            }else{
                $this->response(array('status' => 200, 'message' => 'kepala keluarga tidak ditemukan', 'data' => null));
            }

        }else{
            $this->response(array('status' => 200, 'message' => 'Input tidak sesuai, silahkan cek format request input anda (no kk, id kelas, id pelatihan)!', 'data' => null));
        }
    }

    function detailKader_post(){
        $data = array(
            'id_user' => $this->input->post('id_user')
        );
        $id_user = $this->input->post('id_user');
        $id_kelas = $this->input->post('id_kelas');
        $id_pelatihan = $this->input->post('id_pelatihan');

        if(!empty($data['id_user'])){
            $check_kader = array(
                'role' => 1,
                'id_user' => $id_user
            );
            $data_kader = $this->GeneralApiModel->getWhereTransactional($check_kader, "kader_detail")->row();
            if(!empty($data_kader)){
                $response = array(
                    'id_user' => $data_kader->id_user,
                    'alamat' => $data_kader->alamat_lengkap,
                    'nama' => $data_kader->nama_kader,
                    'nama_kota' => $data_kader->nama_kota,
                    'nama_provinsi' => $data_kader->nama_provinsi,
                    'nohp' => $data_kader->nohp,
                    'status_acc_relawan' => $data_kader->status_acc,
                    'tgl_bergabung' => $data_kader->tgl_join,
                );

                $check_anggota = array(
                    'nomor_kk' => $data_kader->nomor_kk,
                    // 'status_keluarga !=' => 0
                );
                $data_anggota_keluarga = $this->GeneralApiModel->getWhereTransactional($check_anggota, "transactional_anggota_keluarga")->result();
                $i = 0;
                if(!empty($data_anggota_keluarga)){
                    foreach($data_anggota_keluarga as $row){
                        $data_anggota = $this->GeneralApiModel->getWhereTransactional(array('id' => $row->id_user), "transactional_user")->row();

                        $kondisi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_hasil_skrining")->result();
                        $kondisi = ($kondisi?$kondisi[0]:null);
                        $kondisi_fisik = ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_fisik),'masterdata_grading_status_kesehatan')->result()[0]->nama:null);
                        $kondisi_mental = ($kondisi?$return_fisik = $this->GeneralApiModel->getWhereMaster(array('id' => $kondisi->kondisi_mental),'masterdata_grading_status_kesehatan')->result()[0]->nama:null);

                        $presensi = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user), "cdate", "DESC", "transactional_presensi")->result();
                        $presensi = ($presensi?$presensi[0]:null);

                        $kelas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id"=>$id_kelas), "cdate", "DESC", "transactional_kelas")->result()[0];
                        $id_pelatihan = $kelas->id_pelatihan;
                        $aktivitas = $this->GeneralApiModel->getOneWhereTransactionalOrdered(array("id_user"=>$id_user, "id_kelas"=>$id_kelas,"id_pelatihan"=>$id_pelatihan), "cdate", "DESC", "transactional_hasil_aktivitas")->result();
                        $aktivitas = ($aktivitas?$aktivitas[0]:null);

                        $response['anggota_keluarga'][$i]['id_user'] = $row->id_user;
                        $response['anggota_keluarga'][$i]['kondisi_fisik'] = $kondisi_fisik;
                        $response['anggota_keluarga'][$i]['kondisi_mental'] = $kondisi_mental;
                        $response['anggota_keluarga'][$i]['nama'] = $data_anggota->namalengkap;
                        $response['anggota_keluarga'][$i]['nik'] = $row->nik_anggota;
                        $status_keluarga = array("Kepala Keluarga", "Istri", "Anak");
                        $response['anggota_keluarga'][$i]['status_keluarga'] = $status_keluarga[$row->status_keluarga];
                        $response['anggota_keluarga'][$i]['terakhir_isi_aktivitas'] = ($aktivitas?$aktivitas->cdate:null);
                        $response['anggota_keluarga'][$i]['terakhir_presensi'] = ($presensi?$presensi->cdate:null);
                        $response['anggota_keluarga'][$i]['umur'] = floor((time() - strtotime($data_anggota->tgl_lahir)) / 31556926);
                        $i++;
                    }
                }else{
                    $response['anggota_keluarga'] = array();
                }

                $check_binaan = array(
                    'id_pembina' => $data['id_user']
                );
                $data_binaan = $this->GeneralApiModel->getWhereTransactional($check_binaan, "transactional_binaan")->result();
                $j = 0;
                if(!empty($data_binaan)){
                    foreach($data_binaan as $row){
                        $data_keluarga2 = $this->GeneralApiModel->getWhereTransactional(array('nomor_kk' => $row->nomor_kk), "transactional_anggota_keluarga")->result();
                        $data_kepel2 = $this->GeneralApiModel->getWhereTransactional(array('nomor_kk' => $row->nomor_kk, 'status_keluarga' => 0), "transactional_anggota_keluarga")->row();
                        $data_user2 = $data_keluarga = $this->GeneralApiModel->getWhereTransactional(array('id' => $data_kepel2->id_user), "transactional_user")->row();
                        $data_alamat2 = $this->GeneralApiModel->getWhereTransactional(array('nomor_kk' => $row->nomor_kk), "keluargabinaan_detail")->row();

                        $response['keluarga_binaan'][$j]['jml_anggota'] = count($data_keluarga2);
                        $response['keluarga_binaan'][$j]['nama_kepel'] = $data_user2->namalengkap;
                        if(!empty($data_alamat2)){
                            $response['keluarga_binaan'][$j]['nama_kota'] = $data_alamat2->nama_kota;
                            $response['keluarga_binaan'][$j]['nama_provinsi'] = $data_alamat2->nama_provinsi;
                        }else{
                            $response['keluarga_binaan'][$j]['nama_kota'] = "tidak ada";
                            $response['keluarga_binaan'][$j]['nama_provinsi'] = "tidak ada";
                        }
                        $response['keluarga_binaan'][$j]['no_kk'] = $row->nomor_kk;
                        $response['keluarga_binaan'][$j]['tgl_bergabung'] = $row->tgl_join;
                        $j++;
                    }
                }else{
                    $response['keluarga_binaan'] = array();
                }
                $this->response(array('status' => 200, 'message' => 'test', 'data' => $response));
            } else {
                $this->response(array('status' => 200, 'message' => 'Data kader tidak ditemukan', 'data' => null));
            }
        }else{
            $this->response(array('status' => 200, 'message' => 'Input tidak sesuai, silahkan cek format request input anda (id user, id kelas, id pelatihan)!', 'data' => null));
        }
    }

    function gabungKader_post(){
        $data = array(
            'id_user' => $this->input->post('id_user'),
            'id_pelatihan' => $this->input->post('id_pelatihan'),
            'id_kelas' => $this->input->post('id_kelas')
        );
        if(!empty($data['id_user'])  && !empty($data['id_pelatihan']) && !empty($data['id_kelas'])){
            $check_referal = array(
                'id_user' => $data['id_user'],
                'id_pelatihan' => $data['id_pelatihan'],
                'id_kelas' => $data['id_kelas']
            );

            $exist_check_referal = $this->GeneralApiModel->isDataTransactionalExist($check_referal, 'transactional_kode_referal');

            if(!$exist_check_referal){
                $input_referal = array(
                    'role' => 1,
                    'kode_referal' => random_string('alnum', 6),
                    'status_acc' => 0,
                    'status_pembina' => 0,
                    'id_user' => $data['id_user'],
                    'id_pelatihan' => $data['id_pelatihan'],
                    'id_kelas' => $data['id_kelas'],
                    'tgl_join' => $this->dateToday
                );

                $insert_referal = $this->GeneralApiModel->insertTransactional($input_referal, 'transactional_kode_referal');
                $this->response(array('status' => 200, 'message' => 'Anda berhasil bergabung menjadi kader!', 'data' => true));
            }else{
                $this->response(array('status' => 200, 'message' => 'anda telah terdaftar menjadi kader!', 'data' => null));
            }
        }else{
            $this->response(array('status' => 200, 'message' => 'Input tidak sesuai, silahkan cek format request input anda!', 'data' => null));
        }
    }

    function updatekaderByPeserta_post(){
        $data = array(
            'status_acc' => $this->input->post('status_acc'), //0 = ..., 1 = ...
            'id_pembina' => $this->input->post('id_pembina')
        );
        $where = array(
            'id_user' => $this->input->post('id_user'),
            'role' => 1
        );
        if(($data['status_acc'] == 1) && $data['status_acc'] != ''){
            if(!empty($data['id_pembina'])){
                $update_binaan = array(
                    'id_pembina' => $data['id_pembina'],
                    'status_acc' => 1
                );

                $result = $this->GeneralApiModel->updateTransactional($update_binaan, $where, 'transactional_kode_referal');
                if($result){
                    $this->response(array('status' => 200, 'message' => 'ACC Kader oleh peserta telah Berhasil!', 'data' => $result));
                }
                else{
                    $this->response(array('status' => 200, 'message' => 'ACC kader Gagal Dilakukan!', 'data' => null));
                }
            }
            else{
                $this->response(array('status' => 200, 'message' => 'Id Pembina tidak ditemukan! Update Gagal Dilakukan!', 'data' => null));
            }

        }else if(($data['status_acc'] == 0) && $data['status_acc'] != ''){
            $update_binaan = array(
                'id_pembina' => 0,
                'status_acc' => 0
            );

            $result = $this->GeneralApiModel->updateTransactional($update_binaan, $where, 'transactional_kode_referal');
            if($result){
                $this->response(array('status' => 200, 'message' => 'Kader Berhasil ditolak!', 'data' => $result));
            }
            else{
                $this->response(array('status' => 200, 'message' => 'ACC kader Dilakukan!', 'data' => null));
            }
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Status ACC kader tidak ditemukan! Update Gagal Dilakukan!', 'data' => null));
        }
    }

    function updateKeluargaBinaanByPeserta_post(){
        $data = array(
            'status_acc' => $this->input->post('status_acc'), //0 = ..., 1 = ...
            'id_pembina' => $this->input->post('id_pembina')
        );
        $nomor_kk = array(
            'nomor_kk' => $this->input->post('nomor_kk')
        );
        if(($data['status_acc'] == 1) && $data['status_acc'] != ''){
            if(!empty($data['id_pembina'])){
                $update_binaan = array(
                    'id_pembina' => $data['id_pembina'],
                );

                $result = $this->GeneralApiModel->updateTransactional($update_binaan, $nomor_kk, 'transactional_binaan');
                if($result){
                    $this->response(array('status' => 200, 'message' => 'ACC Keluarga Binaan oleh Relawan telah Berhasil!', 'data' => $result));
                }
                else{
                    $this->response(array('status' => 200, 'message' => 'ACC Keluarga Binaan Gagal Dilakukan!', 'data' => null));
                }
            }
            else{
                $this->response(array('status' => 200, 'message' => 'Id Pembina tidak ditemukan! Update Gagal Dilakukan!', 'data' => null));
            }

        }else if(($data['status_acc'] == 0) && $data['status_acc'] != ''){
            $update_binaan = array(
                'id_pembina' => 0,
            );

            $result = $this->GeneralApiModel->updateTransactional($update_binaan, $nomor_kk, 'transactional_binaan');
            if($result){
                $this->response(array('status' => 200, 'message' => 'Keluarga Binaan oleh Relawan Berhasil ditolak!', 'data' => $result));
            }
            else{
                $this->response(array('status' => 200, 'message' => 'ACC Keluarga Binaan Gagal Dilakukan!', 'data' => null));
            }
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Status ACC Relawan tidak ditemukan! Update Gagal Dilakukan!', 'data' => null));
        }
    }
}
