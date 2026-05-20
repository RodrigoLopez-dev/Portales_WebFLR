<?php

class Donaciones
{
    private $db;
    private $total;

    public function __construct($db)
    {
        $this->db = $db;
        $this->fetchData();
    }

    private function fetchData()
    {
        $query = "SELECT sum(monto) as total FROM `donaciones` WHERE `estado_id` = 1";
        $result = $this->db->query($query);
        $row = $result->fetch_row();
        $this->total = $row[0];
    }

    public function getTotal()
    {
        return $this->total;
    }
}

?>