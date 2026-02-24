<?php

declare(strict_types=1);

namespace JTL\Pagination;

/**
 * Class FilterDateRangeField
 * @package JTL\Pagination
 */
class FilterDateRangeField extends FilterField
{
    private string $dStart = '';

    private string $dEnd = '';

    /**
     * @param Filter          $filter
     * @param string|string[] $title
     * @param string          $column
     * @param string          $defaultValue
     * @param string|null     $id
     */
    public function __construct(Filter $filter, $title, string $column, $defaultValue = '', ?string $id = null)
    {
        parent::__construct($filter, 'daterange', $title, $column, $defaultValue, $id);

        $dRange = \explode(' - ', $this->value);

        if (\count($dRange) === 2) {
            $this->dStart = \date_create($dRange[0])->format('Y-m-d') . ' 00:00:00';
            $this->dEnd   = \date_create($dRange[1])->format('Y-m-d') . ' 23:59:59';
        }
    }

    public function getWhereClause(): ?string
    {
        $dRange = \explode(' - ', $this->value);

        if (\count($dRange) === 2) {
            $dStart = \date_create($dRange[0])->format('Y-m-d') . ' 00:00:00';
            $dEnd   = \date_create($dRange[1])->format('Y-m-d') . ' 23:59:59';

            return $this->column . " >= '" . $dStart . "' AND " . $this->column . " <= '" . $dEnd . "'";
        }

        return null;
    }

    public function getStart(): string
    {
        return $this->dStart;
    }

    public function getEnd(): string
    {
        return $this->dEnd;
    }
}
