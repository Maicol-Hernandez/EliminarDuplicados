<?php

namespace Dyalogo\Script\utils;

class EjecutarSQL
{

    public static function runSQLObject(object $mysqli, string $consulta)
    {
        $sql = mysqli_query($mysqli, $consulta);
        if ($sql && mysqli_num_rows($sql) > 0) {
            $result = mysqli_fetch_object($sql);
            // $result = mysqli_fetch_all($sql, MYSQLI_ASSOC);

            // echo "\n runSQL => ", json_encode($result), "\n\n";
        } else {
            $result = [];
        }
        return $result;
    }

    public static function runSQLAll(object $mysqli, string $consulta)
    {
        $sql = mysqli_query($mysqli, $consulta);
        if ($sql && mysqli_num_rows($sql) > 0) {
            $result = mysqli_fetch_all($sql, MYSQLI_ASSOC);
            // $result = mysqli_fetch_all($sql, MYSQLI_ASSOC);

            // echo "\n runSQL => ", json_encode($result), "\n\n";
        } else {
            $result = [];
        }
        return $result;
    }
}
