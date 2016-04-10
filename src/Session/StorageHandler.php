<?php

namespace Meek\Http\Session;

interface StorageHandler
{
    public function open($name);
    public function close($id);
    public function read($id);
    public function write($id, $data);
    public function destroy($id);
    public function gc($maxlifetime);
}
