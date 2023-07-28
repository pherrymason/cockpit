<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use Cockpit\Framework\Database\Constraint;

interface FolderRepository
{
    public function byID(string $folderID): ?Folder;

    public function byConstraint(Constraint $constraints): array;

    public function children(Constraint $constraint, ?string $parentFolderID);

    public function save(Folder $folder): void;

    public function remove(string $folderID);
}
