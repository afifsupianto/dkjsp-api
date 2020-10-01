<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class SkriningApiController extends REST_Controller {
    function __construct($config = 'rest') {
        parent::__construct($config);
        date_default_timezone_set("Asia/Jakarta");
        $this->dateToday = date("Y-m-d");
        $this->timeToday = date("h:i:s");
        $this->load->model("GeneralApiModel");
        $this->load->model("SkriningApiModel");
    }

    function dataSkrining_post(){
        $where = array(
            'id' => $this->input->post('id_skrining')
        );
        $user = array(
            'id' => $this->input->post('id_user')
        );
        if(!empty($where['id']) && !empty($user['id'])){
            $result = $this->GeneralApiModel->getWhereMaster($where, "masterdata_skrining")->row();
            $user = $this->GeneralApiModel->getWhereTransactional($user, "user_provinsi_kota")->row();
            if(!empty($result) && !empty($user)){
                $data = array(
                    'id' => $where['id'],
                    'nama' => $result->nama
                );

                $data['sub_skrining'] = array();
                $where2 = array('id_skrining' => $where['id']);
                $result2 = $this->GeneralApiModel->getWhereMaster($where2, "masterdata_sub_skrining")->result();

                $i = 0;
                $umur = $user->umur;
                foreach($result2 as $row){
                    if($umur < 15 && $i > 1){
                        break;
                    }else{
                        $data['sub_skrining'][$i]['id'] = $row->id;
                        $data['sub_skrining'][$i]['nama'] = $row->nama;
                        $data['sub_skrining'][$i]['deskripsi'] = $row->deskripsi;
                        $data['sub_skrining'][$i]['soal_skrining'] = array();

                        $where3 = array(
                            'id_sub_skrining' => $row->id,
                            'is_child' => 0
                        );
                        $result3 = $this->GeneralApiModel->getWhereMaster($where3, "masterdata_soal_skrining")->result();
                        $j = 0;

                        foreach($result3 as $row){
                            $data['sub_skrining'][$i]['soal_skrining'][$j]['id'] = $row->id;
                            $data['sub_skrining'][$i]['soal_skrining'][$j]['soal'] = $row->soal;
                            $data['sub_skrining'][$i]['soal_skrining'][$j]['tipe'] = $row->tipe;
                            $data['sub_skrining'][$i]['soal_skrining'][$j]['list_jawaban'] = array();

                            $where4 = array('id_soal' => $row->id);
                            $result4 = $this->GeneralApiModel->getWhereMaster($where4, "masterdata_pilihan_jawaban_skrining")->result();
                            $k = 0;
                            foreach($result4 as $row){
                                $data['sub_skrining'][$i]['soal_skrining'][$j]['list_jawaban'][$k]['id'] = $row->id;
                                $data['sub_skrining'][$i]['soal_skrining'][$j]['list_jawaban'][$k]['jawaban'] = $row->jawaban;
                                if($row->id_child_soal != 0){
                                    $data['sub_skrining'][$i]['soal_skrining'][$j]['list_jawaban'][$k]['anak_pertanyaan'] = $this->SkriningApiModel->recursiveAnakSoal(array(), $row->id_child_soal);
                                }else{
                                    $data['sub_skrining'][$i]['soal_skrining'][$j]['list_jawaban'][$k]['anak_pertanyaan'] = null;
                                }
                                $k++;
                            }

                            $j++;
                        }

                        $i++;
                    }
                }

                $this->response(array('status' => 200, 'message' => 'respond test', 'data' => $data));
            }else{
                $this->response(array('status' => 200, 'message' => 'Data skrining atau Data User tidak ditemukan', 'data' => null));
            }
        }else{
            $this->response(array('status' => 200, 'message' => 'Masukkan id skrining terlebih dahulu! data tidak ditemukan', 'data' => null));
        }
    }

    function submitSkrining_post(){
        $data_jawaban = $this->post('data_jawaban');
        $id_skrining = $this->post('id_skrining');
        $id_user = $this->post('id_user');
        $data = array(
            'data_jawaban' => $data_jawaban,
            'id_skrining' => $id_skrining,
            'id_user' => $id_user
        );
        if(!empty($data_jawaban) && !empty($id_skrining) && !empty($id_user)){
            //medapatkan data sub skrining
            $query = $this->GeneralApiModel->getWhereMaster(array('id_skrining' => $id_skrining),'masterdata_sub_skrining')->result();
            //dapatkan jumlah soal tiap sub skrining
            $i = 0;
            foreach($query as $row){
                $i++;
            }

            $this->response(array('status' => 200, 'message' => 'test', 'data' => $i));
        }else{
            $this->response(array('status' => 200, 'message' => 'Masukkan id skrining, id user, dan data jawaban terlebih dahulu! data tidak ditemukan', 'data' => null));
        }
    }
}
