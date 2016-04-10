<?php

namespace Meek\Http;

use Meek\Http\Response;

/**
 * Send a file response (forces the download window to show).
 */
class FileResponse extends Response
{
    private $path;

    public function __construct($path)
    {
        parent::__construct('', 200, []);

        $this->setPath($path);
    }

    public function open()
    {

    }

    public function close()
    {

    }

    public function setPath($path)
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException('');
        }

        $this->path = $path;
        $this->headers['Content-Type'] = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
        $this->headers['Content-Length'] = filesize($path);
        $this->headers['Content-Disposition'] = 'attachment; filename="' . basename($path) . '"';

        return $this;
    }

    public function send()
    {
        parent::send();
        readfile($this->path);

        return $this;
    }
}
