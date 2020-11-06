<?php

class AktivitasApiModel extends CI_Model
{
    function getAktivitasHarian($where){
      $this->db->distinct('cdate');
      $this->db->select('cdate');
      $this->db->where($where);
      $this->db->order_by('cdate','DESC');
      $query = $this->db->get('transactional_hasil_aktivitas');
      return $query;
    }

    // function getAktivitasSatuHari($where){
    function getAktivitasPerSoal($where){
      $this->db->distinct('id_soal');
      $this->db->select('id_soal');
      $this->db->where($where);
      $this->db->order_by('id_soal','ASC');
      $query = $this->db->get('transactional_hasil_aktivitas');
      return $query;
    }
}
