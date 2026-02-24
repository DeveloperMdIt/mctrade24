<?php

declare(strict_types=1);

namespace JTL;

/**
 * Class Plausi
 * @package JTL
 */
class Plausi
{
    protected array $xPostVar_arr = [];

    protected array $xPlausiVar_arr = [];

    public function getPostVar(): array
    {
        return $this->xPostVar_arr;
    }

    public function getPlausiVar(): array
    {
        return $this->xPlausiVar_arr;
    }

    public function setPostVar(array $variables, ?array $hasHTML = null, bool $toEntities = false): bool
    {
        if (\count($variables) === 0) {
            return false;
        }
        if (\is_array($hasHTML)) {
            $excludeKeys = \array_fill_keys($hasHTML, 1);
            $filter      = \array_diff_key($variables, $excludeKeys);
            $excludes    = \array_intersect_key($variables, $excludeKeys);
            if ($toEntities) {
                \array_map('\htmlentities', $excludes);
            }
            $this->xPostVar_arr = \array_merge($variables, $filter, $excludes);
        } else {
            $this->xPostVar_arr = $variables;
        }

        return true;
    }

    public function setPlausiVar(array $variables): bool
    {
        if (\count($variables) === 0) {
            return false;
        }
        $this->xPlausiVar_arr = $variables;

        return true;
    }

    /**
     * @param string|null $type
     * @param bool        $update
     * @return bool
     */
    public function doPlausi(?string $type = null, bool $update = false): bool
    {
        return false;
    }
}
