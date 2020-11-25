<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aktivitas extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model("GeneralApiModel");
		$this->load->library('session');
	}

	function index(){
		if (isset($_POST['simpan'])) {
			$insert['id_aktivitas'] = $_POST['aktivitas'];
			$insert['soal'] = $_POST['soal'];
			$insert['tipe'] = $_POST['tipe'];
			$insert['id_topik'] = $_POST['topik'];
			$id_soal = $this->GeneralApiModel->insertIdMaster($insert, 'masterdata_soal_aktivitas');
			$jawaban = $_POST['jawaban'];
			$list_jawaban = array();
			foreach ($jawaban as $j) {
				array_push($list_jawaban, array('jawaban'=>$j, 'id_soal'=>$id_soal));
			}
			$this->GeneralApiModel->insertBatchMaster($list_jawaban, 'masterdata_pilihan_jawaban_aktivitas');
			$this->session->set_flashdata('sukses_soal', 'Tambah Soal & Jawaban Sukses!');
			redirect(base_url("admin/aktivitas/topik"));
		}
		$data['aktivitas'] = $this->GeneralApiModel->getAllMaster('masterdata_aktivitas')->result();
		$data['topik'] = $this->GeneralApiModel->getAllMaster('masterdata_topik')->result();
		$this->load->view('aktivitas', $data);
	}

	function topik(){
		if (isset($_POST['simpan_topik'])) {
			$insert['nama'] = $_POST['topik'];
			$this->GeneralApiModel->insertMaster($insert, 'masterdata_topik');
			$this->session->set_flashdata('sukses_topik', 'Tambah Soal & Jawaban Sukses!');
			redirect(base_url("admin/aktivitas/"));
		}
		$data['aktivitas'] = $this->GeneralApiModel->getAllMaster('masterdata_aktivitas')->result();
		$data['topik'] = $this->GeneralApiModel->getAllMaster('masterdata_topik')->result();
		$this->load->view('aktivitas', $data);
	}
}
