<?php

class AuthApiModel extends CI_Model{

    function loginByEmail($data){
        //kasih if else; jika role 0, panggil view ... jika role 1, panggil view ... 
        $query = $this->db->get_where('transactional_user',array('email' => $data['user']));
        return $query;
    }

    function loginByHp($data){
        //kasih if else; jika role 0, panggil view ... jika role 1, panggil view ...
        $query = $this->db->get_where('transactional_user',array('nohp' => $data['user']));
        return $query;
    }

    function insertPeserta($data, $data2, $data3){
        $this->db->trans_begin();

        $this->db->insert('transactional_user', $data);
        $insert_id = $this->db->insert_id();

        $status = $this->db->trans_status();
        if ($status === FALSE){
            $this->db->trans_rollback();
        }
        else{
            $data2['id_user'] = $insert_id;
            $this->db->insert('transactional_detail_peserta', $data2);
            $status = $this->db->trans_status();
            if($status === FALSE){
                $this->db->trans_rollback();
            }
            else{
                $data3['id_user'] = $insert_id;
                $this->db->insert('transactional_alamat', $data3);
                $status = $this->db->trans_status();
                
                if($status === FALSE){
                    $this->db->trans_rollback();
                }else{
                    $this->db->trans_commit();
                }
            }
        }
        return $insert_id;
    }
    
    function insertPanitia($data_user, $data_alamat){
        $this->db->trans_begin();

        $this->db->insert('transactional_user', $data_user);
        $id_user = $this->db->insert_id();

        $status = $this->db->trans_status();
        
        if ($status === FALSE){
            $this->db->trans_rollback();
        }
        else{
            $data_alamat['id_user'] = $id_user;
            $this->db->insert('transactional_alamat', $data_alamat);
            $status = $this->db->trans_status();
            
            if($status === FALSE){
                $this->db->trans_rollback();
            }
            else{
                $this->db->trans_commit();
            }
        }
        
        return $id_user;
    }

    function insertKepalaKeluargaBinaan($data_user,$data_keluargabinaan,$data_anggota,$data_alamat){
        $this->db->trans_begin();

        $this->db->insert('transactional_user', $data_user);
        $id_user = $this->db->insert_id();

        $status = $this->db->trans_status();
        
        if ($status === FALSE){
            $this->db->trans_rollback();
        }
        else{
            $this->db->insert('transactional_keluargabinaan', $data_keluargabinaan);
            $status = $this->db->trans_status();
            
            if($status === FALSE){
                $this->db->trans_rollback();
            }
            else{
                $data_anggota['id_user'] = $id_user;
                $this->db->insert('transactional_anggota_keluarga', $data_anggota);
                $status = $this->db->trans_status();
                
                if($status === FALSE){
                    $this->db->trans_rollback();
                }
                else{
                    $data_alamat['id_user'] = $id_user;
                    $this->db->insert('transactional_alamat', $data_alamat);
                    $status = $this->db->trans_status();
                    if($status === FALSE){
                        $this->db->trans_rollback();
                    }
                    else{
                        $this->db->trans_commit();
                    }
                }
            }
        }
        return $id_user;
    }

    function insertAnggotaKeluargaBinaan($data_user, $data_anggota, $data_alamat){
        $this->db->trans_begin();

        $this->db->insert('transactional_user', $data_user);
        $id_user = $this->db->insert_id();

        $status = $this->db->trans_status();
        
        if ($status === FALSE){
            $this->db->trans_rollback();
        }
        else{
            $data_anggota['id_user'] = $id_user;
            $this->db->insert('transactional_anggota_keluarga', $data_anggota);

            $status = $this->db->trans_status();
            
            if($status === FALSE){
                $this->db->trans_rollback();
            }
            else{
                $data_alamat['id_user'] = $id_user;
                $this->db->insert('transactional_alamat', $data_alamat);
                $status = $this->db->trans_status();
                
                if($status === FALSE){
                    $this->db->trans_rollback();
                }
                else{
                    $this->db->trans_commit();
                }
            }
        }
        return $id_user;
    }

    function isEmailExist($data){
        if($this->db->get_where('transactional_user',array('email'=>$data))->result()){
            return true;
        }
        else{
            return false;
        }
    }

    function isKKExist($data){
        if($this->db->get_where('transactional_keluargabinaan',array('nomor_kk'=>$data))->result()){
            return true;
        }
        else{
            return false;
        }
    }

    function isHpExist($data){
        if($this->db->get_where('transactional_user',array('nohp'=>$data))->result()){
            return true;
        }
        else{
            return false;
        }
    }
    
    function getUser($data){
        $query = $this->db->get_where('user', $data);
        return $query;
    }

    function getViewByRole($data){
        //jika role adalah peserta/kader 0
        if($data['role'] == 0){
            $query = $this->db->get_where('user_peserta_detail', $data);
        }
        //jika role adalah keluarga binaan 1
        elseif($data['role'] == 1){
            $query = $this->db->get_where('user_anggotakeluarga_detail', $data);
        }
        //jika role adalah admin 2
        elseif($data['role'] == 2){
            $query = $this->db->get_where('user_admin_detail', $data);
        }
        elseif($data['role'] == 3){
            $query = $this->db->get_where('user_operator_detail', $data);
        }
        elseif($data['role'] == 4){
            $query = $this->db->get_where('user_pemateri_detail', $data);
        }
        return $query;
    }
}

    //CATATAN VIEW

    // CREATE VIEW user_peserta_detail AS
    // SELECT a.id, a.email, a.jenis_kelamin, a.role, a.nohp, a.namalengkap, a.tgl_lahir, a.statusdata, c.nama_provinsi, d.nama_kota
    // FROM ublearni_mhscourses_transactional.transactional_user a
    // LEFT JOIN ublearni_mhscourses_transactional.transactional_detail_peserta b
    // ON a.id = b.id_user
    // LEFT JOIN ublearni_mhscourses_masterdata.masterdata_provinsi c
    // ON b.id_provinsi = c.id_provinsi
    // LEFT JOIN ublearni_mhscourses_masterdata.masterdata_kota d
    // ON b.id_kota = d.id_kota     
    // WHERE a.role = 0

    // CREATE VIEW user_anggotakeluarga_detail AS
    // SELECT a.id, a.email, a.jenis_kelamin, a.role, a.nohp, a.namalengkap, a.tgl_lahir, a.statusdata, b.nik_anggota, b.nomor_kk, b.status_keluarga, b.pekerjaan, b.tempat_kerja, d.nama_provinsi, e.nama_kota
    // FROM ublearni_mhscourses_transactional.transactional_user a
    // JOIN ublearni_mhscourses_transactional.transactional_anggota_keluarga b
    // ON a.id = b.id_user
    // JOIN ublearni_mhscourses_transactional.transactional_keluargabinaan c
    // ON b.nomor_kk = c.nomor_kk
    // LEFT JOIN ublearni_mhscourses_masterdata.masterdata_provinsi d
    // ON c.id_provinsi = d.id_provinsi
    // LEFT JOIN ublearni_mhscourses_masterdata.masterdata_kota e
    // ON c.id_kota = e.id_kota     
    // WHERE a.role = 1