<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminApiModel extends CI_Model {
    function __construct(){
        parent::__construct();
    }

    function getDataJumlahUserKelas(){
        $db = $this->load->database('mhsc_transactional', TRUE);
        
        $query1 = $db->count_all('transactional_kelas');
        $query2 = $db->like('status_pembina', '1');
        $query2 = $db->from('transactional_kode_referal');
        $query2 = $db->count_all_results();
        $query3 = $db->get('counting_user_role')->result();
        
        $data[0]['role'] = "kelas";
        $data[0]['jumlah'] = strval($query1);
        $data[1]['role'] = "kader";
        $data[1]['jumlah'] = strval($query2);
        $i = 2;
        foreach($query3 as $row){
            $key = $row->nama_role;
            $value = $row->jumlah;
            
            if(!is_null($key)){
                $key = strtolower($key);
                $data[$i]['role'] = $key;
                $data[$i]['jumlah'] = $value;
            }
            $i++;
        }
        return $data;
    }
    
    function getDataJumlahUserProvinsi(){
        $db = $this->load->database('mhsc_transactional', TRUE);
        
        $query = $db->get('counting_user_by_provinsi')->result();
        $i = 0;
        foreach($query as $row){
            $key = $row->nama_provinsi;
            $value = $row->jumlah;
            
            if(!is_null($key)){
                $key = strtolower($key);
                $data[$i]['nama'] = $key;
                $data[$i]['jumlah'] = $value;
            }
            $i++;
        }
        
        return $data;
    }
    
    function getDataJumlahUserKota(){
        $db = $this->load->database('mhsc_transactional', TRUE);
        
        $query = $db->get('counting_user_by_kota')->result();
        $i = 0;
        foreach($query as $row){
            $key = $row->nama_kota;
            $value = $row->jumlah;
            
            if(!is_null($key)){
                $key = strtolower($key);
                $data[$i]['nama'] = $key;
                $data[$i]['jumlah'] = $value;
            }
            $i++;
        }
        
        return $data;
    }
}