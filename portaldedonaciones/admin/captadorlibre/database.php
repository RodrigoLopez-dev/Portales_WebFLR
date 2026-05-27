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
            (int) env_value('CAPTADOR_DB_PORT', 3306)
        );

        if ($this->con->connect_error) {
            error_log('Conexión CAPTADOR_DB falló: ' . $this->con->connect_error);

            $appDebug = env_value('APP_DEBUG', 'false');

            if ($appDebug === 'true' || $appDebug === '1') {
                die('Conexión a la base de datos falló: ' . $this->con->connect_error);
            }

            die('No fue posible conectar con la base de datos.');
        }

        $this->con->set_charset('utf8mb4');
    }

    public function create($codigo, $cod_POS, $rut, $nombre, $oficina, $email, $mes_ingreso, $proyecto, $estado)
    {
        $sql = "
            INSERT INTO captador
            (
                codigo,
                cod_POS,
                rut,
                nombre,
                oficina,
                email,
                mes_ingreso,
                proyecto,
                estado
            )
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

        if (!$ok) {
            error_log('Error ejecutando create captador: ' . $stmt->error);
        }

        $stmt->close();

        return $ok;
    }

    public function read()
    {
        $sql = "
            SELECT
                codigo,
                cod_POS,
                rut,
                nombre,
                oficina,
                email,
                mes_ingreso,
                proyecto,
                estado,
                fecha_actualizacion
            FROM captador
            ORDER BY fecha_actualizacion DESC, nombre ASC
        ";

        return $this->con->query($sql);
    }

    public function single_record($rut)
    {
        $sql = "
            SELECT
                codigo,
                cod_POS,
                rut,
                nombre,
                oficina,
                email,
                mes_ingreso,
                proyecto,
                estado,
                fecha_actualizacion
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

    public function update($original_rut, $codigo, $cod_POS, $rut, $nombre, $oficina, $email, $mes_ingreso, $proyecto, $estado)
    {
        $sql = "
            UPDATE captador
            SET
                codigo = ?,
                cod_POS = ?,
                rut = ?,
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
            'ssssssssss',
            $codigo,
            $cod_POS,
            $rut,
            $nombre,
            $oficina,
            $email,
            $mes_ingreso,
            $proyecto,
            $estado,
            $original_rut
        );

        $ok = $stmt->execute();

        if (!$ok) {
            error_log('Error ejecutando update captador: ' . $stmt->error);
        }

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

        if (!$ok) {
            error_log('Error ejecutando delete captador: ' . $stmt->error);
        }

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

            if (!empty($row['ultima_actualizacion'])) {
                return $row['ultima_actualizacion'];
            }
        }

        return 'No disponible';
    }

    public function rut_exists($rut)
    {
        $sql = "
        SELECT rut
        FROM captador
        WHERE rut = ?
        LIMIT 1
    ";

        $stmt = $this->con->prepare($sql);

        if (!$stmt) {
            error_log('Error preparando rut_exists captador: ' . $this->con->error);
            return false;
        }

        $stmt->bind_param('s', $rut);

        if (!$stmt->execute()) {
            error_log('Error ejecutando rut_exists captador: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $stmt->bind_result($rutEncontrado);

        $exists = false;

        if ($stmt->fetch()) {
            $exists = true;
        }

        $stmt->close();

        return $exists;
    }
}