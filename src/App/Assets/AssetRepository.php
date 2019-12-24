<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use Framework\Database\Constraint;

interface AssetRepository
{
    public function byId(string $assetID);

    public function byConstraint(Constraint $constraints);

    public function countAll(): int;

    public function save(Asset $asset);
}
