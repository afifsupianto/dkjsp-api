<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class AuthApiController extends REST_Controller
{

    function __construct($config = 'rest')
    {
        parent::__construct($config);
        date_default_timezone_set("Asia/Jakarta");
        $this->dateToday = date("Y-m-d H:i:s");
        $this->timeToday = date("h:i:s");
        $this->load->model("AuthApiModel");
    }

    //API Register Peserta
    function daftarPeserta_post()
    {
        $data = array(
            'email' => $this->input->post('email'),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'jenis_kelamin' => $this->input->post('jenis_kelamin'), //0 = laki, 1 = perempuan
            'role' => 0, //0 = peserta, 1 = keluarga binaan, 2 = panitia
            'nohp' => $this->input->post('nohp'),
            'namalengkap' => strtolower($this->input->post('namalengkap')),
            'tgl_lahir' => $this->input->post('tgl_lahir'),
            'statusdata' => 0  //0 = data ada, 1 = terhapus atau deleted
        );

        $data2 = array(
            'id_institusi' => $this->input->post('id_institusi'),
            'alamat_institusi' => strtolower($this->input->post('alamat_institusi')), //Alamat tidak master data! biarkan masuk pada form transactional
        );

        $data3 = array(
            'id_provinsi' => $this->input->post('id_provinsi'),
            'id_kota' => $this->input->post('id_kota'),
            'id_kecamatan' => $this->input->post('id_kecamatan'),
            'id_desa' => $this->input->post('id_desa'),
            'alamat_lengkap' => $this->input->post('alamat_lengkap')
        );

        $email_exist = $this->AuthApiModel->isEmailExist($data['email']);
        $nohp_exist = $this->AuthApiModel->isHpExist($data['nohp']);
        if ($email_exist || $nohp_exist) {
            if ($email_exist) {
                $this->response(array('status' => 200, 'message' => 'Email Telah Terdaftar!', 'data' => null));
            } else if ($nohp_exist) {
                $this->response(array('status' => 200, 'message' => 'No. HP Telah Terdaftar!', 'data' => null));
            }
        } else {
            $result = $this->AuthApiModel->insertPeserta($data, $data2, $data3); //return id yg terdaftar
            if (!empty($result)) {
                $view = array(
                    'id' => $result,
                    'role' => 0
                );
                $result2 = $this->AuthApiModel->getViewByRole($view)->result();
                if(!empty($result2)){
                    $this->response(array('status' => 200, 'message' => 'Registrasi sukses!', 'data' => $result2[0]));
                }
                else{
                    $this->response(array('status' => 200, 'message' => 'Data pada DB view tidak tersedia', 'data' => null));
                }
            }
        }
    }

    //API Register Keluarga Binaan
    function daftarKeluargaBinaan_post()
    {
        //data User
        $data_user = array(
            'namalengkap' => strtolower($this->input->post('namalengkap')),
            'email' => $this->input->post('email'),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'role' => 1, //0 = peserta, 1 = keluarga binaan, 2 = panitia
            'jenis_kelamin' => $this->input->post('jenis_kelamin'), //0 = laki, 1 = perempuan
            'nohp' => $this->input->post('nohp'),
            'tgl_lahir' => $this->input->post('tgl_lahir'),
            'statusdata' => 0 //0 = data ada, 1 = terhapus atau deleted
        );

        //Data Anggota Keluarga
        $data_anggota = array(
            'nik_anggota' => $this->input->post('nik'),
            'nomor_kk' => $this->input->post('nomor_kk'),
            'status_keluarga' => $this->input->post('status_keluarga'), //kepala keluarga = 0, isteri = 1, anak = 2;
            'pekerjaan' => strtolower($this->input->post('pekerjaan')),
            'tempat_kerja' => strtolower($this->input->post('tempat_kerja')),
            'statusdata' => 0
        );

        $data_alamat = array(
            'id_provinsi' => $this->input->post('id_provinsi'),
            'id_kota' => $this->input->post('id_kota'),
            'id_kecamatan' => $this->input->post('id_kecamatan'),
            'id_desa' => $this->input->post('id_desa'),
            'alamat_lengkap' => $this->input->post('alamat_lengkap'),
            'nama_ketua_rt' => $this->input->post('nama_ketua_rt'),
            'nama_ketua_rw' => $this->input->post('nama_ketua_rw')
        );

        $email_exist = $this->AuthApiModel->isEmailExist($data_user['email']);
        $nohp_exist = $this->AuthApiModel->isHpExist($data_user['nohp']);
        if ($email_exist || $nohp_exist) {
            if ($email_exist) {
                $this->response(array('status' => 200, 'message' => 'Email Telah Terdaftar!', 'data' => null));
            } else if ($nohp_exist) {
                $this->response(array('status' => 200, 'message' => 'No. HP Telah Terdaftar!', 'data' => null));
            }
        } else {
            $kk_exist = $this->AuthApiModel->isKKExist($data_anggota['nomor_kk']);
            if ($kk_exist) {
                if ($data_anggota['status_keluarga'] == 0) {
                    $this->response(array('status' => 200, 'message' => 'Register Gagal! Anda telah terdaftar sebagai kepala keluarga', 'data' => null));
                } else {
                    $result = $this->AuthApiModel->insertAnggotaKeluargaBinaan($data_user, $data_anggota, $data_alamat);
                    if (!empty($result)) {
                        $view = array(
                            'id' => $result,
                            'role' => 1
                        );
                        $result2 = $this->AuthApiModel->getViewByRole($view)->result();
                        $format = array(
                            'id' => $result2[0]->id,
                            'email' => $result2[0]->email,
                            'jenis_kelamin' => $result2[0]->jenis_kelamin,
                            'role' => $result2[0]->role,
                            'nohp' => $result2[0]->nohp,
                            'namalengkap' => $result2[0]->namalengkap,
                            'tgl_lahir' => $result2[0]->tgl_lahir,
                            'statusdata' => $result2[0]->statusdata,
                            'nama_provinsi' => $result2[0]->nama_provinsi,
                            'nama_kota' => $result2[0]->nama_kota,
                            'nama_kecamatan' => $result2[0]->nama_kecamatan,
                            'nama_desa' => $result2[0]->nama_desa
                        );
                        if(!empty($result2)){
                            $this->response(array('status' => 200, 'message' => 'Register sukses! Anda terdaftar sebagai Anggota Keluarga', 'data' => $format));
                        }
                        else{
                            $this->response(array('status' => 200, 'message' => 'Data pada DB view tidak tersedia', 'data' => null));
                        }
                    }
                }
            } else {
                if ($data_anggota['status_keluarga'] == 0) {
                    //Data Kepala Keluarga
                    $data_keluargabinaan = array(
                        'nomor_kk' => $this->input->post('nomor_kk'),
                        // 'id_pembina' => 0,
                    );
                    $result = $this->AuthApiModel->insertKepalaKeluargaBinaan($data_user, $data_keluargabinaan, $data_anggota, $data_alamat);
                    if (!empty($result)) {
                        $view = array(
                            'id' => $result,
                            'role' => 1
                        );
                        $result2 = $this->AuthApiModel->getViewByRole($view)->result();
                        $format = array(
                            'id' => $result2[0]->id,
                            'email' => $result2[0]->email,
                            'jenis_kelamin' => $result2[0]->jenis_kelamin,
                            'role' => $result2[0]->role,
                            'nohp' => $result2[0]->nohp,
                            'namalengkap' => $result2[0]->namalengkap,
                            'tgl_lahir' => $result2[0]->tgl_lahir,
                            'statusdata' => $result2[0]->statusdata,
                            'nama_provinsi' => $result2[0]->nama_provinsi,
                            'nama_kota' => $result2[0]->nama_kota,
                            'nama_kecamatan' => $result2[0]->nama_kecamatan,
                            'nama_desa' => $result2[0]->nama_desa
                        );
                        if(!empty($result2)){
                            $this->response(array('status' => 200, 'message' => 'Register sukses! Anda terdaftar sebagai Kepala Keluarga', 'data' => $format));
                        }
                        else{
                            $this->response(array('status' => 200, 'message' => 'Data pada DB view tidak tersedia', 'data' => null));
                        }
                    }
                } else {
                    $this->response(array('status' => 200, 'message' => 'Register Gagal! Kepala Keluarga belum terdaftar', 'data' => null));
                }
            }
        }
    }

    //API Register Panitia
    function daftarPanitia_post()
    {
        $data_user = array(
            'namalengkap' => strtolower($this->input->post('namalengkap')),
            'email' => $this->input->post('email'),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'role' => $this->input->post('role'),
            'jenis_kelamin' => $this->input->post('jenis_kelamin'), //0 = laki, 1 = perempuan
            'nohp' => $this->input->post('nohp'),
            'tgl_lahir' => $this->input->post('tgl_lahir'),
            'statusdata' => 0 //0 = data ada, 1 = terhapus atau deleted
        );

        $data_alamat = array(
            'id_provinsi' => $this->input->post('id_provinsi'),
            'id_kota' => $this->input->post('id_kota'),
            'id_kecamatan' => $this->input->post('id_kecamatan'),
            'id_desa' => $this->input->post('id_desa'),
            'alamat_lengkap' => $this->input->post('alamat_lengkap'),
            'nama_ketua_rt' => $this->input->post('nama_ketua_rt'),
            'nama_ketua_rw' => $this->input->post('nama_ketua_rw')
        );

        $email_exist = $this->AuthApiModel->isEmailExist($data_user['email']);
        $nohp_exist = $this->AuthApiModel->isHpExist($data_user['nohp']);

        if ($email_exist || $nohp_exist) {
            if ($email_exist) {
                $this->response(array('status' => 200, 'message' => 'Email Telah Terdaftar!', 'data' => null));
            } else if ($nohp_exist) {
                $this->response(array('status' => 200, 'message' => 'No. HP Telah Terdaftar!', 'data' => null));
            }
        }else{
            $id = $this->AuthApiModel->insertPanitia($data_user, $data_alamat); //return id yg terdaftar
            if (!empty($id)) {
                $view = array(
                    'id' => $id,
                    'role' => $data_user['role']
                );
                $result2 = $this->AuthApiModel->getViewByRole($view)->result();
                if(!empty($result2)){
                    if($view['role'] == 2){
                        $message = "Registrasi anda sebagai panitia admin telah berhasil!";
                    }elseif($view['role'] == 3){
                        $message = "Registrasi anda sebagai panitia operator telah berhasil!";
                    }elseif($view['role'] == 4){
                        $message = "Registrasi anda sebagai panitia pemateri telah berhasil!";
                    }
                    $this->response(array('status' => 200, 'message' => $message, 'data' => $result2[0]));
                }else{
                    $this->response(array('status' => 200, 'message' => 'Data pada DB view tidak tersedia', 'data' => null));
                }
            }
        }

    }

    //API Login
    function prosesLogin_post()
    {
        $data = array(
            'user' => $this->input->post('user'),
            'password' => $this->input->post('password')
        );

        if (filter_var($data['user'], FILTER_VALIDATE_EMAIL)) {
            $result = $this->AuthApiModel->loginByEmail($data)->result();
            if ($result) {
                if (password_verify($data['password'], $result[0]->password)) {
                    $view = array(
                        'id' => $result[0]->id,
                        'role' => $result[0]->role
                    );
                    $result2 = $this->AuthApiModel->getViewByRole($view)->result();
                    if(!empty($result2)){
                        $this->response(array('status' => 200, 'message' => 'Login sukses!', 'data' => $result2[0]));
                    }else{
                        $this->response(array('status' => 200, 'message' => 'Data tidak tersedia', 'data' => null));
                    }
                } else {
                    $this->response(array('status' => 200, 'message' => 'Password Anda Salah!', 'data' => null));
                }
            } else {
                $this->response(array('status' => 200, 'message' => 'Email Belum Terdaftar!', 'data' => null));
            }
        } else {
            $result = $this->AuthApiModel->loginByHp($data)->result();
            if ($result) {
                if (password_verify($data['password'], $result[0]->password)) {
                    $view = array(
                        'id' => $result[0]->id,
                        'role' => $result[0]->role
                    );
                    $result2 = $this->AuthApiModel->getViewByRole($view)->result();
                    if(!empty($result2)){
                        $this->response(array('status' => 200, 'message' => 'Login sukses!', 'data' => $result2[0]));
                    }
                    else{
                        $this->response(array('status' => 200, 'message' => 'Data pada DB view tidak tersedia', 'data' => null));
                    }
                } else {
                    $this->response(array('status' => 200, 'message' => 'Password Anda Salah!', 'data' => null));
                }
            } else {
                $this->response(array('status' => 200, 'message' => 'No. HP Belum Terdaftar!', 'data' => null));
            }
        }
    }
}
