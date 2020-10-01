<?php

class PesertaApiModel extends CI_Model
{

    function getPesertaByReferal($data){
        $query = $this->db->get_where('user_peserta_detail', $data);
        return $query;
    }
}
