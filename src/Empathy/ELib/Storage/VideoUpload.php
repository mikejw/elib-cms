<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;

/**
 * Legacy upload model used by DSection controller video handlers.
 */
class VideoUpload extends Entity
{
    public string $error = '';

    public string $file = '';

    public function upload(): void
    {
    }

    public function make_flv(): void
    {
    }

    public function generateThumb(): void
    {
    }
}
