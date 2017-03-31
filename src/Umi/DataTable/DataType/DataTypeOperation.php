<?php

namespace YM\Umi\DataTable\DataType;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use YM\Models\FieldDisplayBrowser;
use YM\Models\Table;
use YM\Umi\Contracts\DataType\DataTypeInterface;
use YM\Umi\FactoryDataType;

class DataTypeOperation
{
    private $bread;
    private $tableName;
    private $tableId;
    private $minute;

    #database table name
    private $browser = 'umi_field_display_browser';
    private $read = 'umi_field_display_read';
    private $edit = 'umi_field_display_edit';
    private $delete = 'umi_field_display_delete';

    public function __construct($bread, $tableName)
    {
        switch ($bread) {
            case 'browser':
                $this->bread = $this->browser;
                break;
            case 'read':
                $this->bread = $this->read;
                break;
            case 'edit':
                $this->bread = $this->edit;
                break;
            case 'delete':
                $this->bread = $this->delete;
                break;
            default:
                throw new \Exception('wrong parameter is provided');
        }

        $this->tableName = $tableName;

        $this->minute = Config::get('umi.cache_minutes');

        $table = new Table();
        $this->tableId = $table->getTableId($this->tableName);
    }

    private function getDataSet($tableId)
    {
        return Cache::remember('dataSetBrowser' . $tableId, $this->minute, function () use ($tableId){
            return FieldDisplayBrowser::where('table_id', $tableId)
                ->where('is_showing', 1)->get();
        });
    }

    #仅仅为数据浏览所用 just use for the browser
    public function getTHead()
    {
        return $this->getDataSet($this->tableId);
    }

    #获取所有用于显示的字段 get all fields that use for showing on the browser
    public function getFields()
    {
        return $this->getDataSet($this->tableId)
            ->map(function ($item){
                return $item->field;
            })->toArray();
    }

    private function getRegulatingType()
    {
        return $this->getDataSet($this->tableId)
            ->map(function ($item) {
                return [$item->field => $item->type];
            });
    }

    #根据数据类型重写数据格式 get regulated data according to the custom required
    public function regulatedDataSet($dataSet)
    {
        $regulatingFields = $this->getRegulatingType();
        $factory = new FactoryDataType();
        foreach ($regulatingFields as $field) {var_dump($field);
            //$factoryDataType = $factory->getDataType($dataType);
            //dd($factoryDataType->showField());
        }

        return $dataSet;
    }
}