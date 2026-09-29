<?php

class Pagination
{
    private int $page;
    private int $perPage;
    private int $total;

    public function __construct(int $page, int $perPage, int $total)
    {
        $this->page = max(1, $page);
        $this->perPage = max(1, $perPage);
        $this->total = max(0, $total);
    }

    public function getPage(): int
    {
        $totalPages = $this->getTotalPages();
        return min($this->page, max(1, $totalPages));
    }

    public function getOffset(): int
    {
        return ($this->getPage() - 1) * $this->perPage;
    }

    public function getTotalPages(): int
    {
        if ($this->total === 0) {
            return 1;
        }

        return (int) ceil($this->total / $this->perPage);
    }
}
