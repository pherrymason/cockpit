<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use Framework\Database\Constraint;

interface FolderRepository
{
    public function all(Constraint $constraint);
}
