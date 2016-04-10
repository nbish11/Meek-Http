<?php

namespace Meek\Http\Session;

use Meek\Http\Session\StorageHandler;
use PDO;

class PdoStorageDriver implements StorageHandler
{
    private $dbh;

    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    public function open($name)
    {

    }

    public function close($id)
    {

    }

    public function read($id)
    {
        $sql = "SELECT data, lifetime, time FROM sessions WHERE id = :id FOR UPDATE";

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        $sessionRows = $stmt->fetchAll(PDO::FETCH_NUM);

        if ($sessionRows) {
            if ($sessionRows[0][1] + $sessionRows[0][2] < time()) {
                return '';
            }

            return is_resource($sessionRows[0][0]) ?
                   stream_get_contents($sessionRows[0][0]) :
                   $sessionRows[0][0];
        }
    }

    public function write($id, $data)
    {
        $maxlifetime = (integer) ini_get('session.gc_maxlifetime');

        $sql = "INSERT INTO sessions (id, data, lifetime, time) VALUES (:id, :data, :lifetime, :time) ".
        "ON DUPLICATE KEY UPDATE data = VALUES(data), lifetime = VALUES(lifetime), time = VALUES(time)";

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->bindParam(':data', $data, PDO::PARAM_LOB);
        $stmt->bindParam(':lifetime', $maxlifetime, PDO::PARAM_INT);
        $stmt->bindValue(':time', time(), PDO::PARAM_INT);
        $stmt->execute();
    }

    public function destroy($id)
    {
        $sql = "DELETE FROM sessions WHERE id = :id";

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function gc($maxlifetime)
    {
        $sql = "DELETE FROM sessions WHERE lifetime + time < :time";

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(':time', time(), PDO::PARAM_INT);
        $stmt->execute();
    }
}
