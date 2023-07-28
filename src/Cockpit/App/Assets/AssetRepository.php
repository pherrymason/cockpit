<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use Cockpit\Framework\Database\Constraint;

interface AssetRepository
{
    public function byId(string $assetID): ?array;

    /**
     * @param Constraint $constraints
     * @return Asset[]
     */
    public function byConstraint(Constraint $constraints): array;

    public function countAll(): int;

    public function save(Asset $asset, string $folderID = null);

    public function delete(string $assetID);
}
