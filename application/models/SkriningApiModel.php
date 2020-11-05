<?php

class SkriningApiModel extends CI_Model
{

    function recursiveAnakSoal($obj, $id){
        $db = $this->load->database('mhsc_masterdata', TRUE);
        $result = $db->get_where('masterdata_soal_skrining', array('id' => $id, 'is_child' => 1))->row();
        $data = array();
        if(!empty($result)){
            $data['id'] = $result->id;
            $data['nama'] = $result->soal;
            $data['tipe'] = $result->tipe;
            $data['list_jawaban'] = array();

            $result2 = $db->get_where('masterdata_pilihan_jawaban_skrining', array('id_soal' => $result->id))->result();
            $i = 0;
        // }


        // if(!empty($result2)){
            foreach($result2 as $row){
                $data['list_jawaban'][$i]['id'] = $row->id;
                $data['list_jawaban'][$i]['jawaban'] = $row->jawaban;
                if($row->id_child_soal != 0){
                    $data['list_jawaban'][$i]['anak_pertanyaan'] = $this->recursiveAnakSoal($data['list_jawaban'][$i], $row->id_child_soal);
                }else{
                    $data['list_jawaban'][$i]['anak_pertanyaan'] = null;
                }
                $i++;
            }
        }

        $data_obj = $obj;
        $data_obj=$data;
        return $data_obj;

        //if($data === 0){
        //    return 1;
        //}else{
        //    return $data * $this->recursiveAnakSoal($data-1);
        //}
    }

    function getAllSkrining($where){
      $this->db->distinct('cdate');
      $this->db->select('cdate');
      $this->db->where($where);
      $this->db->order_by('cdate','DESC');
      $query = $this->db->get('transactional_hasil_skrining');
      return $query;
    }
}
