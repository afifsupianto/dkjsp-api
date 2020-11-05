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

    function getAktivitasSatuHari($where, $cdate){
      $this->db->distinct('cdate');
      $this->db->select('cdate');
      $this->db->where($where);
      $this->db->order_by('cdate','DESC');
      $query = $this->db->get('transactional_hasil_aktivitas');
      return $query;
    }
}
