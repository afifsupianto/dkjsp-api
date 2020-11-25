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

	function lihat($id_aktivitas=null){
		$data['aktivitas'] = $this->GeneralApiModel->getAllMaster('masterdata_aktivitas')->result();
		$html = '';
		if (!empty($id_aktivitas)) {
			$aktivitas = $this->GeneralApiModel->getWhereMaster(array('id_aktivitas'=>$id_aktivitas),'detail_soal_aktivitas')->result();
			$id_topik = 0;
			$id_soal = 0;
			$id_jawaban = 0;
			$html .= "<h1 align='center'> Aktivitas ".$aktivitas[0]->nama."</h1>";
			foreach ($aktivitas as $a) {
				if ($id_topik!=$a->id_topik) {
					$html .= "<br/><hr><br/><h2>$a->topik</h2>";
				}
				if ($id_soal!=$a->id_soal) {
					$html .= "<br/><h4>$a->soal</h4>";
				}
				if ($id_jawaban!=$a->id_jawaban) {
					$html .= "<li style='padding-left:20px;'>$a->jawaban</li>";
				}
				$id_jawaban = $a->id_jawaban;
				$id_soal = $a->id_soal;
				$id_topik = $a->id_topik;
			}
		}
		$html .= '<br/><br/>';
		$data['list'] = serialize($html);
		$this->load->view('lihat_aktivitas', $data);
	}
}
