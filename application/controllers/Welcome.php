<?php
ini_set('memory_limit', '-1');
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *        http://example.com/index.php/welcome
     *    - or -
     *        http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index()
    {
        $this->load->model("mp_model");
        $this->getConstituency();
    }
    public function indexOther()
    {
        $this->load->database();
        $this->load->model("mp_model");
        //$result = $this->mp_model->scrapeOldDB();
        //$this->exctractPost($result);
        $result = $this->mp_model->getBillsUpdated();
        $this->getBillsUpdated($result);
    }


    function getBillsUpdated($content)
    {
        $bills_array = array();
        foreach ($content as $item) {
            $bill = new bill();
            $bill->name = $item->post_title;
            $bill->description = strip_tags($item->post_content);
            $bill->link = $item->guid;
            array_push($bills_array, $bill);
        }
        echo json_encode($bills_array);
    }

    function getConstituency()
    {
        $cons = array();
        $total = $this->mp_model->getConstituency();
        foreach ($total as $item) {
            if (!in_array($item->meta_value, $cons)) {
                array_push($cons, $item->meta_value);
            }
        }
        echo json_encode($cons);
    }

    function getCM($content)
    {
        $cm_array = array();
        foreach ($content as $item) {
            $cm = new cm();
            $cm->name = strip_tags($item->post_title);
            $cm->description = strip_tags($item->post_content);
            array_push($cm_array, $cm);
        }
        echo json_encode($cm_array);
    }

    function getBills($content)
    {
        $bills_array = array();
        $dom = new domDocument;
        libxml_use_internal_errors(true);
        foreach ($content as $contents) {
            $subject = $contents->post_content;
            $bill = new bill();
            //echo $subject . "\n";
            if (strlen($subject) > 5) {
                try {
                    if ($dom->loadHTML($subject)) {
                        $rows = $dom->getElementsByTagName('li');
                        for ($row = 0; $row < $rows->length; $row++) {
                            $item = explode("-", $this->replace($rows->item($row)->nodeValue));
                            $bill->name = $item[0];
                            $bill->description = isset($item[1]) ? $item[1] : "";
                            array_push($bills_array, $bill);
                        }
                    }

                } catch (DOMException $e) {

                }

            }
        }
        echo json_encode($bills_array);
    }

    function replace($string)
    {
        $total = array("[rad-hl]", "/rad-hl]", "[/st_label]", "[st_label]", "[st_label type='warning']", "[/st_label type='warning']", "[st_label type='notice']", "[st_label type='success']", "Â", '<span style="font-family: Bookman Old Style,serif;"><span style="font-size: medium;">');
        foreach ($total as $item) {
            $string = str_replace($item, "", $string);
        }
        return $string;
    }

    public function mpImport()
    {
        //$result = $this->mp_model->scrapeOldDB();
        //$this->exctractPost($result);
        //var_dump($this->mp_model->scrapeOldDB());
    }


    function exctractPost($content)
    {
        $mp_array = array();
        foreach ($content as $contents) {
            $dom = new domDocument;
            //echo $contents->post_title;
            $subject = $contents->post_content;
            $mp = new mp();
            $mp->name = $contents->post_title;
            if (strlen($subject) > 1) {
                $dom->loadHTML($subject);
                $rows = $dom->getElementsByTagName('td');
                for ($row = 0; $row < $rows->length; $row++) {
                    //var_dump($rows->item($row)->nodeValue);
                    $cQuery = $this->db->query("SELECT * FROM `pwun_postmeta` WHERE `meta_key`='constituency' AND post_id=" . $contents->ID);
                    $res = $cQuery->result();
                    foreach ($res as $result) {
                        $mp->constituency = $result->meta_value;
                    }
                    if (preg_match("/Political/", $rows->item($row)->nodeValue)) {
                        $mp->political_party = $rows->item($row + 1)->nodeValue != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Gender/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->gender = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Marital Status/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->marital_status = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Email Address/", $rows->item($row + 1) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->email_address = $rows->item($row + 1)->nodeValue != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Postal Address/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->postal_address = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Mobile Telephone/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->mobile_telephone = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Profession/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->profession = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Date Of Birth/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->date_of_birth = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Religion/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->religion = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Landline/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->landline = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Special interests/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->special_interests = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Other Responsibilites/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->other_responsibilities = $rows->item($row + 1) != null ? $rows->item($row + 1)->nodeValue : "";
                    }
                    if (preg_match("/Education/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->education = array();
                        $row = $row + 1;
                        if (preg_match("/1/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->education, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/2/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->education, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/3/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->education, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/4/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->education, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/5/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->education, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/6/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->education, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                    }
                    if (preg_match("/Work History/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                        $mp->work_history = array();
                        $row = $row + 1;
                        if (preg_match("/1/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->work_history, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/2/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->work_history, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/3/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->work_history, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/4/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->work_history, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/5/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->work_history, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }
                        if (preg_match("/6/", $rows->item($row) != null ? $rows->item($row)->nodeValue : " ")) {
                            $row = $row + 1;
                            array_push($mp->work_history, $rows->item($row) != null ? $rows->item($row)->nodeValue : "");
                            $row = $row + 1;
                        }


                    }
                }
            };
            array_push($mp_array, $mp);
        }
        echo json_encode($mp_array);

    }

    function exctractImage($search_phrase)
    {

        $table = "pwun_postmeta";
        $sql_search = "select * from " . $table . " where ";
        $sql_search_fields = Array();
        $sql = "SHOW COLUMNS FROM " . $table;
        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            foreach ($result->result_array() as $row) {
                $column = $row['Field'];
                $sql_search_fields[] = $column . " like('%" . $search_phrase . "%')";

            }
        }
        $sql_search .= implode(" OR ", $sql_search_fields);
        $result = $this->db->query($sql_search);
        var_dump($result->result_array());

    }

    function exctractPosts($content)
    {
        foreach ($content as $contents) {
            $dom = new domDocument;
            //echo $contents->post_title;
            $subject = $contents->post_content;
            $mp = new mp();
            $mp->name = $contents->post_title;
            if (strlen($subject) > 1) {
                $dom->loadHTML($subject);
                $rows = $dom->getElementsByTagName('td');
                json_encode($rows);
                foreach ($rows as $row) {
                    //var_dump($row->nodeValue);
                    echo count($rows);
                }


            };
        }
        var_dump($mp);

    }

    function mapToObject($mp, $value)
    {


    }

}

class cm
{

    public $name;
    public $description;
}

class bill
{
    public $name;
    public $description;
    public $link;

}

class mp
{
    public $name;
    public $political_party;
    public $constituency;
    public $gender;
    public $marital_status;
    public $email_address;
    public $postal_address;
    public $mobile_telephone;
    public $profession;
    public $date_of_birth;
    public $education;
    public $religion;
    public $work_history;
    public $landline;
    public $special_interests;
    public $other_responsibilities;

}
