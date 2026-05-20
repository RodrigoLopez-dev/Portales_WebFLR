<?php

class Alcancia
{
    private $db;
    private $codigo;
    private $nombre;
    private $tipo;
    private $total;

    public function __construct($db, $codigo)
    {
        $this->db = $db;
        $this->codigo = $codigo;
        $this->fetchData();
    }
    private function fetchData()
    {
        if (is_numeric($this->codigo)) {
            $this->nombre = 'N° ' . $this->codigo;
            $this->tipo = 'alcancia';
        } else {
            $result = $this->db->query(sprintf("SELECT nombre, tipo FROM alcancias WHERE codigo = '%s'", mysqli_real_escape_string($this->db, $this->codigo)));
            $row = $result->fetch_row();

            if (empty($row[0])) {
                echo '<script>alert("Lo sentimos esta alcancia no existe.");window.location=".";</script>';
                exit;
            }

            $this->nombre = $row[0];
            $this->tipo = $row[1];
        }

        $result = $this->db->query(sprintf("SELECT total FROM v_alcancias WHERE utm_source = '%s'", mysqli_real_escape_string($this->db, $this->codigo)));
        $row2 = $result->fetch_row();
        $this->total = $row2[0];
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getTotal()
    {
        return $this->total;
    }
}

?>