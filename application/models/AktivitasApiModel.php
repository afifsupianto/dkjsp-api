<?php

class AktivitasApiModel extends CI_Model
{
    function getAktivitasHarian($where){
      $this->db->distinct('cdate');
      $this->db->select('cdate');
      $this->db->where($where);
      $query = $this->db->get('transactional_hasil_aktivitas');
      return $query;
    }
}
