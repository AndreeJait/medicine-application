<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class Pagination
{

    private int $offset;
    private int $limit;
    public int $page;
    public int $perPage;

    /**
     * Create a new class instance.
     */
    public function __construct(
        Request $request
    )
    {
        $this->page = $request->get('page');
        $this->perPage = $request->get('per_page');

        if($this->page <= 0) {
            $this->page = 1;
        }
        if($this->perPage <= 0) {
            $this->perPage = 10;
        }
        $this->offset = ($this->page - 1) * $this->perPage;
        $this->limit = $this->perPage;
    }

    public function getOffset() : int {
        return $this->offset;
    }

    public function getLimit() : int {
        return $this->limit;
    }

    public function buildPaginationResponse($items = [], $totalData = 0, $shownTotal = false) : array {
        $data = [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'items' => $items,
        ];
        if($shownTotal) {
            $data['total_data'] = $totalData;
            $data['total_pages'] = ceil($totalData / $this->perPage);
        }else {
            $data['has_next'] = count($items) >= $this->perPage;
        }
        return $data;
    }
}
