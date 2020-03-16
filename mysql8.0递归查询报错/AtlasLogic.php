<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/13 0013
 * Time: 16:18
 */

namespace logicmodel;



use think\Db;

class AtlasLogic
{

    private $curTableName;

    public function __construct($viewname)
    {

        $this->curTableName = $viewname;

    }


    /**
     * 获取上级推荐关系
     * @param $nodeId :主键ID
     * @param array $fields :需要的字段，必须含有pid
     * @param $startLevel :开始代
     * @param $endLevel :结束代
     * @return mixed
     */
    public function listParent($nodeId, array $fields, $startLevel, $endLevel)
    {
        return $this->listBase($nodeId, $fields, 'up', 1, $startLevel, $endLevel);
    }

    /**
     * 获取上级节点信息
     * @param $nodeId :主键ID
     * @param array $fields :需要的字段，必须含有upid
     * @param $startLevel :开始层
     * @param $endLevel :结束层
     * @return mixed
     */
    public function listUpNode($nodeId, array $fields, $startLevel = 0, $endLevel = 0)
    {

        return $this->listBase($nodeId, $fields, 'up', 2, $startLevel, $endLevel);

    }

    /**
     * 获取下级推荐成员
     * @param $nodeId :主键ID
     * @param array $fields :需要的字段，必须含有pid
     * @param $startLevel :开始代
     * @param $endLevel :结束代
     * @return mixed
     */
    public function listChild($nodeId, array $fields, $startLevel, $endLevel)
    {

        return $this->listBase($nodeId, $fields, 'down', 1, $startLevel, $endLevel);
    }

    /**
     * 获取下级节点成员
     * @param $nodeId :主键ID
     * @param array $fields :需要的字段，必须含有upid
     * @param $startLevel :开始层
     * @param $endLevel :结束层
     * @return mixed
     */
    public function listDownNode($nodeId, array $fields, $startLevel = 0, $endLevel = 0)
    {

        return $this->listBase($nodeId, $fields, 'down', 2, $startLevel, $endLevel);
    }


    /**
     * @param $nodeId
     * @param int $type :1 为推荐关系，2为节点关系
     * @return mixed
     */
    private function listBase($nodeId, array $fields, $directioin = 'down', $type = 1, $startLevel = 0, $endLevel = 0)
    {
        $sql = $this->buildNodeSql($type, $directioin, $fields);
        if ($startLevel === 0 && $endLevel === 0) {
            $sql = $sql . " select * from getNodes";
        } else if ($startLevel > 0 && $endLevel > 0 && $startLevel <= $endLevel) {
            $sql = $sql . " select * from getNodes where cur_level between {$startLevel} and {$endLevel}";

        } else if ($startLevel > 0) {
            $sql = $sql . " select * from getNodes  where cur_level>={$startLevel}";
        }

        return Db::query($sql, ['id' => $nodeId]);

    }

    private function buildNodeSql($type = 1, $direction = 'up', array $fileds = ['*'])
    {
        $column = 'pid';
        if ($type === 2) {
            $column = 'upid';
        }
        $filedStr = "";
        $aliasFiledStr = "";
        $sql = '';
        foreach ($fileds as $columnName) {
            $filedStr = $filedStr . $columnName . ',';
            $aliasFiledStr = $aliasFiledStr . 'e.' . $columnName . ',';
        }

        $sql = "WITH RECURSIVE getNodes as (
  SELECT {$filedStr} 1 'cur_level',CONCAT('->',member) 'node_path' from {$this->curTableName} where id=:id UNION ALL ";
        if ($direction === 'up') {
            $sql = $sql . "SELECT {$aliasFiledStr} c.cur_level+1 'cur_level', CONCAT(c.node_path,'->',e.member) 'node_path' FROM {$this->curTableName} e JOIN getNodes c on e.id=c.{$column}) ";
        } else if ($direction === 'down') {
            $sql = $sql . "SELECT {$aliasFiledStr} c.cur_level+1 'cur_level', CONCAT(c.node_path,'->',e.member) 'node_path' FROM {$this->curTableName} e JOIN getNodes c on e.{$column}=c.id) ";
        }

        return $sql;


    }
}