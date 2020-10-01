<?php

class KeluargaBinaanApiModel extends CI_Model{

    function getDataBinaanByKK($data){
        $query = $this->db->get_where('keluargabinaan_detail',array('nomor_kk' => $data));
        return $query;

        // Buat View keluargabinaan_detail
        // CREATE VIEW keluargabinaan_detail AS
        // SELECT a.*, b.nama_provinsi, c.nama_kota 
        // FROM ublearni_mhscourses_transactional.transactional_keluargabinaan a
        // JOIN ublearni_mhscourses_masterdata.masterdata_provinsi b
        // ON a.id_provinsi = b.id_provinsi
        // JOIN ublearni_mhscourses_masterdata.masterdata_kota c
        // ON a.id_kota = c.id_kota
    }

    function updateKeluargaBinaan($data, $nomor_kk){
        $this->db->trans_begin();

        $this->db->where($nomor_kk);
        $this->db->update('transactional_keluargabinaan', $data);

        $status = $this->db->trans_status();
        
        if ($status === FALSE){
            $this->db->trans_rollback();
        }
        else{
            $this->db->trans_commit();
        }
        return $status;
    }
}