<?php

namespace Meek\Http;

use Meek\Http\Session\StorageHandler;

class Session
{
    const DEFAULT_SESSION_NAME = "MEEKSESSID";

    // only generate a new id when starting a new session or when calling the migrate method.
    const ID_GENERATE_NONE = 1;

    // generate a new id on every request until data has been saved
    const ID_GENERATE_UNTIL_DATA = 2;

    // generate a new id on every single request
    const ID_GENERATE_EVERY_REQUEST = 3;

    private $handler;
    private $name = self::DEFAULT_SESSION_NAME;
    private $id;
    private $data = [];
    private $idGeneration = self::ID_GENERATE_NONE;

    private $gcMaxlifetime = 180;
    private $gcProbability = 1;
    private $gcDivisor = 100;

    public function __construct(StorageHandler $handler, $idGeneration = self::ID_GENERATE_NONE)
    {
        $this->handler = $handler;
        $this->idGeneration = $idGeneration;
    }

    /**
     * Starts a new session.
     * @return [type] [description]
     */
    public function start()
    {
        $this->handler->open($this->name);

        $this->load();
    }

    /**
     * Attempts to load a previous session.
     * @return [type] [description]
     */
    public function load()
    {
        if (array_key_exists($this->name, $_COOKIE)) {
            $data = $this->handler->read($_COOKIE[$this->name]);

            if ($data) {
                $this->id = $_COOKIE[$this->name];
                $this->data = static::unserialize($data);

                switch ($this->idGeneration) {
                    case self::ID_GENERATE_NONE:
                        break;
                    case self::ID_GENERATE_UNTIL_DATA:
                        if (empty($this->data)) {
                            $this->destroy();
                        }
                        break;
                    case self::ID_GENERATE_EVERY_REQUEST:
                        $this->migrate();
                }


            }
        }
    }

    /**
     * Retrieves a value from the session.
     * @param  [type] $key     [description]
     * @param  [type] $default [description]
     * @return [type]          [description]
     */
    public function get($key, $default = null)
    {
        return $this->exists($key) ? $this->data[$key] : $default;
    }

    /**
     * Stores a value in the sesssion.
     * @param [type] $key   [description]
     * @param [type] $value [description]
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Checks if the key already exists in the session.
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Removes a value from the session.
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function remove($key)
    {
        if ($this->exists($key)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Destroys the current session.
     * @return [type] [description]
     */
    public function destroy()
    {
        $this->data = [];
        setcookie($this->name, $this->id, 1, '/', '', false, true);
        $this->handler->destroy($this->id);
        $this->id = null;
    }

    /**
     * Saves the current session.
     * @param  [type] $response [description]
     * @return [type]           [description]
     */
    public function save()
    {
        // save, store, commit
        $data = static::serialize($this->data);

        if ($this->id || $data) {
            if ($this->id === null) {
                $this->id = static::uuidv4();

                $this->handler->read($this->id);
                setcookie($this->name, $this->id, 0, '/', '', false, true);
            }

            $this->handler->write($this->id, $data);
        }

        if (mt_rand(1, $this->gcDivisor) <= $this->gcProbability) {
            $this->handler->gc($this->gcMaxlifetime);
        }
    }

    /**
     * Create a new session with the current data and remove the old session.
     * @return [type] [description]
     */
    public function migrate()
    {
        $oldId = $this->id;
        $data = $this->data;
        $this->destroy();
        $this->id = static::uuidv4();
        $this->data = $data;
        setcookie($this->name, $this->id, 0, '/', '', false, true);
    }

    public function clear()
    {
        $this->data = [];

        return $this;
    }

    protected static function serialize($data)
    {
        return serialize($data);
    }

    protected static function unserialize($data)
    {
        return unserialize($data);
    }

    private function uuidv4()
    {
        $entropy = openssl_random_pseudo_bytes(16);
        $r = unpack('v*', $entropy);

        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            $r[1], $r[2], $r[3], $r[4] & 0x0fff | 0x4000,
            $r[5] & 0x3fff | 0x8000, $r[6], $r[7], $r[8]);
    }
}
