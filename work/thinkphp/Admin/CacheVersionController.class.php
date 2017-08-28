<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-27
 * Time: 上午9:59
 */

namespace Admin\Controller;


class CacheVersionController extends GlobalController
{
    public function index($model = '', $return = false)
    {
        $db_name = C('DB_NAME');
        $all_table_names = M()->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA ='{$db_name}'");
        $all_table_names = extractArrVal($all_table_names, 'TABLE_NAME');
        $list = parent::index($model, true);
        $list['all_table_names'] = $all_table_names;
        $this->assign('save_url', U('Admin/CacheVersion/save'));
        $this->assign('list', $list);
        $this->display();
    }
    
    public function save()
    {
        $tables = trim(I('post.tables'), ',');
        $tables = explode(',', $tables);
        $tables = array_filter($tables);
        empty($tables) && $this->error("请选择要更新的表！");
        $flag = true;
        foreach ($tables as $table) {
            $flag = $flag && M()->saveCacheVersion($table);
        }
        if ($flag)
            $this->success('缓存版本号更新成功！');
        else
            $this->error('Redis异常, 更新失败！');
    }
}