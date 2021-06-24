<?php


namespace modelHandle;


use think\Model;
use think\facade\Request;

/**
 * 模型通用方法操作类
 * Class ModelHandle
 * @package czh\modelHandle
 */
class ModelHandle
{

    protected $model;

    protected $with = [];

    /**
     * ModelHandle constructor.
     * @param string|Model $modelName
     */
    public function __construct($modelName)
    {
        if (is_string($modelName)) {
            $this->model = new $modelName();
        }
        if ($modelName instanceof Model) {
            $this->setModel($modelName);
        }
    }

    /**
     * @param string|Model $modelName
     * @return ModelHandle
     */
    public static function instance($modelName)
    {
        return new static($modelName);
    }

    public function setModel($model)
    {
        $this->model = &$model;
    }

    /**
     * @return Model
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * 通用单表查询
     * @param array $condition
     * @param string $order
     * @param string $field
     * @param int $limit
     * @return mixed
     */
    public function getList($condition = [], $field = "*", $order = '', $limit = 0)
    {
        return $this->getBuildQuery($condition, $field, $order)->limit($limit)->select();
    }

    /**
     * 通用单表查询
     * @param array $condition
     * @param string $order
     * @param string $column
     * @param string $key
     * @return mixed
     */
    public function getColumn($condition = [], $column = "*", $key = '', $order = '')
    {
        return $this->getBuildQuery($condition, $column, $order)->column($column, $key);
    }

    /**
     * 通用单表分页查询
     * @param array $condition
     * @param string $order
     * @param string $field
     * @param int $limit
     * @return mixed
     */
    public function getPageList($condition = [], $field = "*", $order = '', $limit = 10)
    {
        return $this->getBuildQuery($condition, $field, $order)->paginate($limit);
    }

    /**
     * 生成通用 query 对象
     * @param array $condition
     * @param string $order
     * @param string $field
     * @return mixed
     */
    public function getBuildQuery($condition = [], $field = '*', $order = '')
    {
        $query = $this->model->field($field);
        if (!empty($condition)) {
            $query = $query->where($condition);
        }
        if (empty($order)) {
            $order = $this->model->getPk() . " DESC";
        }
        if ($with = $this->getWith()) {
            $query->with($with);
        }
        return $query->order($order);
    }

    /**
     * 通用获取单表详情
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return mixed
     */
    public function getInfo($condition = [], $field = '*', $order = '')
    {
        return $this->getBuildQuery($condition, $order)->field($field)->find();
    }

    /**
     * 通用获取单表详情
     * @param string $pk
     * @param string $field
     * @return mixed
     */
    public function getByPk($pk, $field = '*')
    {
        return $this->getBuildQuery([$this->model->getPk() => $pk], $field)->find();
    }

    /**
     * @param $pk
     * @param $value
     * @return mixed
     */
    public function getValueByPk($pk, $value)
    {
        return $this->getBuildQuery([$this->model->getPk() => $pk])->value($value);
    }

    /**
     * @param $condition
     * @param $value
     * @return mixed
     */
    public function getValue($condition, $value)
    {
        return $this->getBuildQuery($condition)->value($value);
    }

    /**
     * 通用统计数量
     * @param array $condition
     * @return mixed
     */
    public function getCount($condition = [])
    {
        return $this->model->where($condition)->count();
    }

    /**
     * 通用按条件删除
     * @param $condition
     * @return bool
     */
    public function del($condition)
    {
        return $this->model->where($condition)->delete();
    }

    /**
     * 通用检查字段唯一
     * @param array $condition
     * @return bool
     */
    public function checkUnique(array $condition): bool
    {
        return (bool)$this->model->where($condition)->count();
    }


    public function getWith()
    {
        return $this->with;
    }

    public function setWith($with)
    {
        $this->with = $with;
        return $this;
    }


    /**
     * 自增
     * @param $column
     * @param array $where
     * @param int $step
     * @return bool
     * @author cjc 2021/6/4 6:36 下午
     */
    public function increase($column, $where = [], $step = 1)
    {
        $res = $this->model->where($where)->setInc($column, $step);
        if ($res)
            return true;
        else
            return false;
    }

    /**
     * 快捷匹配条件
     * @param $field
     * @param $callback
     * @param $except
     * @return $this
     */
    public function when($field, $callback = null, $except = [])
    {
        $value = Request::param($field);
        $this->model = $this->model->when(!is_null($value) && !in_array($value, $except),
            is_callable($callback)
                ? $callback($this->model)
                : function ($query) use ($field, $value) {
                $query->where($field, $value);
            });
        return $this;
    }
}