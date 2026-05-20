<?php

require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

class Database
{
    private $con;

    public function __construct()
    {
        $this->connect_db();
    }

    public function connect_db()
    {
        $this->con = new mysqli(
            env_value('CAPTADOR_DB_HOST', 'localhost'),
            env_value('CAPTADOR_DB_USERNAME', ''),
            env_value('CAPTADOR_DB_PASSWORD', ''),
            env_value('CAPTADOR_DB_DATABASE', ''),
            env_value('CAPTADOR_DB_PORT', 3306)
        );

        if ($this->con->connect_error) {
            die('Conexión a la base de datos falló: ' . $this->con->connect_error);
        }

        $this->con->set_charset('utf8');
    }

    public function sanitize($var)
    {
        return trim($var);
    }

    public function create($codigo, $cod_POS, $rut, $nombre, $oficina, $email, $mes_ingreso, $proyecto, $estado)
    {
        $sql = "
            INSERT INTO captador
            (codigo, cod_POS, rut, nombre, oficina, email, mes_ingreso, proyecto, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->con->prepare($sql);

        if (!$stmt) {
            error_log('Error preparando create captador: ' . $this->con->error);
            return false;
        }

        $stmt->bind_param(
            'sssssssss',
            $codigo,
            $cod_POS,
            $rut,
            $nombre,
            $oficina,
            $email,
            $mes_ingreso,
            $proyecto,
            $estado
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function read()
    {
        $sql = "
            SELECT *
            FROM captador
            ORDER BY codigo DESC
        ";

        return $this->con->query($sql);
    }

    public function single_record($rut)
    {
        $sql = "
            SELECT *
            FROM captador
            WHERE rut = ?
            LIMIT 1
        ";

        $stmt = $this->con->prepare($sql);

        if (!$stmt) {
            error_log('Error preparando single_record captador: ' . $this->con->error);
            return false;
        }

        $stmt->bind_param('s', $rut);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_object() : false;

        $stmt->close();

        return $row;
    }

    public function update($codigo, $cod_POS, $rut, $nombre, $oficina, $email, $mes_ingreso, $proyecto, $estado)
    {
        $sql = "
            UPDATE captador
            SET codigo = ?,
                cod_POS = ?,
                nombre = ?,
                oficina = ?,
                email = ?,
                mes_ingreso = ?,
                proyecto = ?,
                estado = ?
            WHERE rut = ?
        ";

        $stmt = $this->con->prepare($sql);

        if (!$stmt) {
            error_log('Error preparando update captador: ' . $this->con->error);
            return false;
        }

        $stmt->bind_param(
            'sssssssss',
            $codigo,
            $cod_POS,
            $nombre,
            $oficina,
            $email,
            $mes_ingreso,
            $proyecto,
            $estado,
            $rut
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function delete($rut)
    {
        $sql = "
            DELETE FROM captador
            WHERE rut = ?
        ";

        $stmt = $this->con->prepare($sql);

        if (!$stmt) {
            error_log('Error preparando delete captador: ' . $this->con->error);
            return false;
        }

        $stmt->bind_param('s', $rut);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function get_last_update()
    {
        $sql = "
            SELECT MAX(fecha_actualizacion) AS ultima_actualizacion
            FROM captador
        ";

        $res = $this->con->query($sql);

        if ($res) {
            $row = $res->fetch_assoc();
            return !empty($row['ultima_actualizacion']) ? $row['ultima_actualizacion'] : 'No disponible';
        }

        return 'No disponible';
    }
}