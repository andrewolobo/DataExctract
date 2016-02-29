<?php

/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 29-01-2016
 * Time: 15:04
 */
class mp_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

    }
    function scrapeOldDB(){
        $query = "SELECT * FROM `pwun_posts` WHERE `post_type` LIKE 'mp'";
        $result = $this->db->query($query);
        return $result->result();
    }
    function getBills(){
        $query = "SELECT * FROM `pw2_posts` WHERE post_title = 'Bills'";
        $result = $this->db->query($query);
        return $result->result();
    }
    function getBillsUpdated(){
        $query = "SELECT * FROM `pwun_posts` WHERE `post_name` LIKE '%bill%'";
        $result = $this->db->query($query);
        return $result->result();
    }
    function getCommittees(){
        $query =  "SELECT * FROM `parliame_ug`.`pwun_posts` WHERE post_type LIKE '%committee%'";
        $result = $this->db->query($query);
        return $result->result();
    }
    function getConstituency(){
        $query =  "SELECT * FROM `pwun_postmeta` WHERE `meta_key` = 'constituency'";
        $result = $this->db->query($query);
        return $result->result();
    }
}