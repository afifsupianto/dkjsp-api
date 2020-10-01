<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class MasterApiController extends REST_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
        date_default_timezone_set("Asia/Jakarta"); 
        $this->dateToday = date("Y-m-d H:i:s");
        $this->timeToday = date("h:i:s");
        $this->load->model("GeneralApiModel");
    }

    /* -------------------------------- API UNTUK Mobile ---------------------- */
    function showDaftarProvinsi_get(){
        $time_expired=60*60*24*3;
        $time_aweek=$time_expired*2;
        header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
        $result = $this->GeneralApiModel->getAllMaster("masterdata_provinsi")->result();
        if(!empty($result)){
            $this->response(array('status' => 200, 'message' => 'Data Berhasil Ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Data Provinsi Tidak Ditemukan!', 'data' => null));
        }
    }

    function showDaftarKota_get(){
        $time_expired=60*60*24*3;
        $time_aweek=$time_expired*2;
        header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
        $id = array(
            'id_provinsi'=>$this->uri->segment(4)
        );
        $result = $this->GeneralApiModel->getWhereMaster($id, "masterdata_kota")->result();
        if(!empty($result)){
            $this->response(array('status' => 200, 'message' => 'Data Berhasil Ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Data Kota Tidak Ditemukan!', 'data' => null));
        }
    }
    
    function showDaftarKecamatan_get(){
        $time_expired=60*60*24*3;
        $time_aweek=$time_expired*2;
        header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
        $id = array(
            'id_kota'=>$this->uri->segment(4)
        );
        $result = $this->GeneralApiModel->getWhereMaster($id, "masterdata_kecamatan")->result();
        if(!empty($result)){
            $this->response(array('status' => 200, 'message' => 'Data Berhasil Ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Data Kota Tidak Ditemukan!', 'data' => null));
        }
    }
    
    function showDaftarDesa_get(){
        $time_expired=60*60*24*3;
        $time_aweek=$time_expired*2;
        header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
        $id = array(
            'id_kecamatan'=>$this->uri->segment(4)
        );
        $result = $this->GeneralApiModel->getWhereMaster($id, "masterdata_desa")->result();
        if(!empty($result)){
            $this->response(array('status' => 200, 'message' => 'Data Berhasil Ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Data Kota Tidak Ditemukan!', 'data' => null));
        }
    }
    
    function showDaftarProvinsiInstitusi_get(){
        $time_expired=60*60*24*3;
        $time_aweek=$time_expired*2;
        header("Cache-Control: public,max-age=$time_expired,s-maxage=$time_aweek");
        $data_provinsi = $this->GeneralApiModel->getAllMaster('masterdata_provinsi')->result();
        $data_instansi = $this->GeneralApiModel->getAllMaster('masterdata_institusi')->result();
        $data = array(
            'data_provinsi' => $data_provinsi,
            'data_instansi' => $data_instansi
        );
        if(!empty($data_provinsi) && !empty($data_instansi)){
            $this->response(array('status' => 200, 'message' => 'Data Berhasil Ditemukan!', 'data' => $data));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Data Kota Tidak Ditemukan!', 'data' => null));
        }
    }

    /* -------------------------------- CRUD Master Pelatihan ---------------------- */
    function masukkanMasterPelatihan_post(){
        $data = array(
            'nama' => $this->input->post('nama'),
            'deskripsi' => $this->input->post('deskripsi')
        );

        $exist_check = array(
            'nama' => $data['nama']
        );

        $nama_exist = $this->GeneralApiModel->isDataMasterExist($exist_check, "masterdata_pelatihan");
        if($nama_exist){
            $this->response(array('status' => 200, 'message' => 'Judul pelatihan sudah tersedia!', 'data' => null));
        }else{
            $insert = $this->GeneralApiModel->insertMaster($data, "masterdata_pelatihan");
            $this->response(array('status' => 200, 'message' => 'Input pelatihan sukses!', 'data' => $insert));
        }
    }
    
    //Note: untuk return datatable tidak boleh null! karena akan error pada JQUERY nya!. return querynya saja
    function showSemuaMasterPelatihan_get(){
        $result = $this->GeneralApiModel->getAllMaster("masterdata_pelatihan")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data pelatihan berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data pelatihan ditemukan!', 'data' => $result));
        }
    }

    function showDetailMasterPelatihan_post(){
        $data = array(
            'id' => $this->input->post('id')
        );
        $result = $this->GeneralApiModel->getWhereMaster($data, "masterdata_pelatihan")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data pelatihan berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data pelatihan ditemukan!', 'data' => null));
        }
    }

    function editMasterPelatihan_post(){
        $id = array(
            'id' => $this->input->post('id')
        );
        $data = array(
            'nama' => $this->input->post('nama'),
            'deskripsi' => $this->input->post('deskripsi')
        );
        $update = $this->GeneralApiModel->updateMaster($data, $id, "masterdata_pelatihan");
        if($update){
            $this->response(array('status' => 200, 'message' => 'Edit pelatihan sukses!', 'data' => $update));
        }else{
            $this->response(array('status' => 200, 'message' => 'Update pelatihan gagal dilakukan!', 'data' => null));
        }
    }

    function hapusMasterPelatihan_post(){
        $data = array(
            'id' => $this->input->post('id')
        );
        $result = $this->GeneralApiModel->deleteMaster($data, "masterdata_pelatihan");
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data master pelatihan berhasil dihapus!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Data pelatihan gagal dihapus!', 'data' => null));
        }
    }

    /* -------------------------------- CRUD Master Materi ---------------------- */

    function masukkanMasterMateri_post(){
        $data = array(
            'judul' => $this->input->post('judul'),
            'id_pelatihan' => $this->input->post('id_pelatihan'),
            'statusdata' => 0
        );

        $exist_check = array(
            'judul' => $data['judul']
        );

        $nama_exist = $this->GeneralApiModel->isDataMasterExist($exist_check, "masterdata_materi");
        if($nama_exist){
            $this->response(array('status' => 200, 'message' => 'Judul materi sudah tersedia!', 'data' => null));
        }else{
            $insert = $this->GeneralApiModel->insertMaster($data, "masterdata_materi");
            $this->response(array('status' => 200, 'message' => 'Input  materi Sukses!', 'data' => $insert));
        }
    }
    
    //Note: untuk return datatable tidak boleh null! karena akan error pada JQUERY nya!. return querynya saja
    function showSemuaMasterMateri_get(){
        $result = $this->GeneralApiModel->getAllMaster("masterdata_materi")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data materi berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data materi yang ditemukan!', 'data' => $result));
        }
    }

    function showDetailMasterMateri_post(){
        $data = array(
            'id' => $this->input->post('id')
        );
        $result = $this->GeneralApiModel->getWhereMaster($data, "masterdata_materi")->result();
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data materi berhasil ditemukan!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Tidak ada data materi yang ditemukan!', 'data' => null));
        }
    }

    function editMasterMateri_post(){
        $id = array(
            'id' => $this->input->post('id')
        );
        $data = array(
            'judul' => $this->input->post('nama'),
            'id_pelatihan' => $this->input->post('deskripsi')
        );
        $update = $this->GeneralApiModel->updateMaster($data, $id, "masterdata_materi");
        if($update){
            $this->response(array('status' => 200, 'message' => 'Edit Materi Sukses!', 'data' => $update));
        }else{
            $this->response(array('status' => 200, 'message' => 'Update Materi gagal dilakukan!', 'data' => null));
        }
    }

    function hapusMasterMateri_post(){
        $data = array(
            'id' => $this->input->post('id')
        );
        $result = $this->GeneralApiModel->deleteMaster($data, "masterdata_materi");
        if($result){
            $this->response(array('status' => 200, 'message' => 'Data Master Materi berhasil dihapus!', 'data' => $result));
        }
        else{
            $this->response(array('status' => 200, 'message' => 'Data gagal dihapus!', 'data' => null));
        }
    }
}