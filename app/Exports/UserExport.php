<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;  // 指定使用数组结构
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings; // 设置excel的首行对应的表头信息
use Maatwebsite\Excel\Concerns\WithMapping; // 设置excel中每列要展示的数据


class UserExport implements FromCollection, WithHeadings, WithMapping, Responsable
{
    use Exportable;
    protected $data;
    private $fileName;

    public function __construct(array $data)
    {
        //实例化该脚本的时候，需要传入要导出的数据
        $this->data = $data;
    }

    /**
     * 将数组转为集合
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::all();
    }

    /**
     * // 返回的数据
     * @return array
     */
    public function array():array
    {
        return $this->data;

    }

    public function toResponse($request)
    {

    }

    /**
     * 指定excel的表头
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            '名字',
        ];
    }

    /**
     * 指定excel中每一列的数据字段
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['id'],
            $row['name'],
        ];
    }
}
