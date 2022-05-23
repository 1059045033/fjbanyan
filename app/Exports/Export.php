<?php
namespace App\Exports;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Export implements WithHeadings, WithEvents,FromCollection
{
    protected $data;
    protected $headings;
    protected $columnWidth = [];      //设置列宽       key：列  value:宽
    protected $rowHeight = [];        //设置行高       key：行  value:高
    protected $mergeCells = [];       //合并单元格    value:A1:K8
    protected $font = [];             //设置字体       key：A1:K8  value:Arial
    protected $fontSize = [];         //设置字体大小       key：A1:K8  value:11
    protected $bold = [];             //设置粗体       key：A1:K8  value:true
    protected $background = [];       //设置背景颜色    key：A1:K8  value:#F0F0F0F
    protected $vertical = [];         //设置定位       key：A1:K8  value:center
    protected $sheetName;             //sheet title
    protected $borders = [];          //设置边框颜色  key：A1:K8  value:#000000
    //设置页面属性时如果无效               更改excel格式尝试即可

    protected $images = [];            //excle中的图片组
    //构造函数传值
    public function __construct($data, $headings,$sheetName)
    {
        $this->data = $data;
        $this->headings = $headings;
        $this->sheetName = $sheetName;
        $this->createData();
    }

    public function headings(): array
    {
        return $this->headings;
    }

    //数组转集合
    public function collection()
    {
        return new Collection($this->data);
    }
    //业务代码
    public function createData()
    {
        $this->images = [];
        foreach ($this->data as $k=>$v){
            if(!empty($v['atlas_arr'])){
                $this->images[$k]['atlas_arr'] = $v['atlas_arr'];
                $this->data[$k]['atlas_arr']   = null;
            }
        }

        $this->data = collect($this->data)->toArray();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class  => function(AfterSheet $event) {
                foreach ($this->images as $k => $v){
                    if(!empty($v['atlas_arr']))
                    {
                        foreach ($v['atlas_arr'] as $kk => $vv)
                        {
                            //echo "图集 {$k} 的第".($kk+1)."张图\n";
                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                            $drawing->setName('task');
                            $drawing->setDescription('task_images');
                            $drawing->setPath(public_path($vv));
                            $drawing->setHeight(70);
                            $drawing->setWidth(60);
                            $drawing->setCoordinates('J'.($k+2));
                            $drawing->setOffsetX(20 + 65 * $kk);
                            $drawing->setOffsetY(10);
                            $drawing->setWorksheet($event->sheet->getDelegate());
                        }
                    }
                }


                //设置区域单元格垂直居中
                $event->sheet->getDelegate()->getStyle('A1:Z1265')->getAlignment()->setVertical('center');
                //设置区域单元格水平居中
                $event->sheet->getDelegate()->getStyle('A1:Z1265')->getAlignment()->setHorizontal('center');
                //设置列宽
                foreach ($this->columnWidth as $column => $width) {
                    $event->sheet->getDelegate()
                        ->getColumnDimension($column)
                        ->setWidth($width);
                }
                //设置行高，$i为数据行数
                foreach ($this->rowHeight as $row => $height) {
                    $event->sheet->getDelegate()
                        ->getRowDimension($row)
                        ->setRowHeight($height);
                }

                //设置区域单元格垂直居中
                foreach ($this->vertical as $region => $position) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getAlignment()
                        ->setVertical($position);
                }

                //设置区域单元格字体
                foreach ($this->font as $region => $value) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getFont()->setName($value);
                }
                //设置区域单元格字体大小
                foreach ($this->fontSize as $region => $value) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getFont()
                        ->setSize($value);
                }

                //设置区域单元格字体粗体
                foreach ($this->bold as $region => $bool) {
                    $event->sheet->getDelegate()
                        ->getStyle($region)
                        ->getFont()
                        ->setBold($bool);
                }


                //设置区域单元格背景颜色
                foreach ($this->background as $region => $item) {
                    $event->sheet->getDelegate()->getStyle($region)->applyFromArray([
                        'fill' => [
                            'fillType' => 'linear', //线性填充，类似渐变
                            'startColor' => [
                                'rgb' => $item //初始颜色
                            ],
                            //结束颜色，如果需要单一背景色，请和初始颜色保持一致
                            'endColor' => [
                                'argb' => $item
                            ]
                        ]
                    ]);
                }
                //设置边框颜色
                foreach ($this->borders as $region => $item) {
                    $event->sheet->getDelegate()->getStyle($region)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' =>Border::BORDER_THIN,
                                'color' => ['argb' => $item],
                            ],
                        ],
                    ]);
                }
                //合并单元格
                $event->sheet->getDelegate()->setMergeCells($this->mergeCells);
                if(!empty($this->sheetName)){
                    $event->sheet->getDelegate()->setTitle($this->sheetName);
                }
            }
        ];
    }

    /**
     * @return array
     * [
     *    'B' => 40,
     *    'C' => 60
     * ]
     */
    public function setColumnWidth (array $columnwidth)
    {
        $this->columnWidth = array_change_key_case($columnwidth, CASE_UPPER);
    }

    /**
     * @return array
     * [
     *    1 => 40,
     *    2 => 60
     * ]
     */
    public function setRowHeight (array $rowHeight)
    {
        $this->rowHeight = $rowHeight;
    }

    /**
     * @return array
     * [
     *    A1:K7 => '宋体'
     * ]
     */
    public function setFont (array $font)
    {
        $this->font = array_change_key_case($font, CASE_UPPER);
    }

    /**
     * @return array
     * @2020/3/22 10:33
     * [
     *    A1:K7 => true
     * ]
     */
    public function setBold (array $bold)
    {
        $this->bold = array_change_key_case($bold, CASE_UPPER);
    }

    /**
     * @return array
     * @2020/3/22 10:33
     * [
     *    A1:K7 => F0FF0F
     * ]
     */
    public function setBackground (array $background)
    {
        $this->background = array_change_key_case($background, CASE_UPPER);
    }
    /**
     * @return array
     * [
     *    A1:K7
     * ]
     */
    public function setMergeCells (array $mergeCells)
    {
        $this->mergeCells = array_change_key_case($mergeCells, CASE_UPPER);
    }
    /**
     * @return array
     * [
     *    A1:K7 => 14
     * ]
     */
    public function setFontSize (array $fontSize)
    {
        $this->fontSize = array_change_key_case($fontSize, CASE_UPPER);
    }
    /**
     * @return array
     * [
     *    A1:K7 => #000000
     * ]
     */
    public function setBorders (array $borders)
    {
        $this->borders = array_change_key_case($borders, CASE_UPPER);
    }

//    public function drawings()
//    {
//
//        $drawing = new Drawing();
//        $drawing->setName('Logo');
//        $drawing->setDescription('This is my logo');
//        $drawing->setPath(public_path('/faces/2.png'));
//        $drawing->setHeight(50);
//        $drawing->setCoordinates('B5');
//
//        $drawing2 = new Drawing();
//        $drawing2->setName('Logo');
//        $drawing2->setDescription('This is my logo');
//        $drawing2->setPath(public_path('/faces/2.png'));
//        $drawing2->setHeight(50);
//        $drawing2->setCoordinates('B6');
//
//
//        return [$drawing,$drawing2];
//    }
}
