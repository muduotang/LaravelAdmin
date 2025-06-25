<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExampleController extends Controller
{
    /**
     * 成功响应示例
     */
    public function successExample()
    {
        $data = [
            'name' => 'example',
            'description' => 'This is a success response example'
        ];

        return $this->success($data, 'Data retrieved successfully');
    }

    /**
     * 错误响应示例
     */
    public function errorExample()
    {
        return $this->error('Something went wrong', 400);
    }

    /**
     * 分页响应示例
     */
    public function paginateExample()
    {
        $data = collect(range(1, 100))->map(function ($i) {
            return [
                'id' => $i,
                'name' => "Item {$i}"
            ];
        });

        $page = request('page', 1);
        $perPage = request('per_page', 10);
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $data->forPage($page, $perPage),
            $data->count(),
            $perPage,
            $page
        );

        return $this->paginate($paginator, 'Paginated data retrieved successfully');
    }

    /**
     * 集合响应示例
     */
    public function collectionExample()
    {
        $collection = collect([
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
        ]);

        return $this->collection($collection, 'Collection data retrieved successfully');
    }
}
