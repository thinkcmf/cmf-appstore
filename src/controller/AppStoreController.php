<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\HookModel;
use app\admin\model\PluginModel;
use app\admin\model\HookPluginModel;
use cmf\paginator\Bootstrap;
use think\Db;

/**
 * 应用市场
 * @adminMenuRoot(
 *     'name'   =>'应用市场',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 20,
 *     'icon'   =>'cloud',
 *     'remark' =>'应用市场'
 * )
 */
class AppStoreController extends AdminBaseController
{
    /**
     * 应用市场首页
     * @adminMenu(
     *     'name'   => '应用市场首页',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '应用市场首页',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 插件市场
     * @adminMenu(
     *     'name'   => '插件市场',
     *     'parent' => 'index',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '插件市场',
     *     'param'  => ''
     * )
     */
    public function plugins()
    {
        $currentPage = $this->request->param('page', 1, 'intval');
        $data        = cmf_curl_get("https://www.thinkcmf.com/api/appstore/plugins?pass=222222222222&page=" . $currentPage);

        $data = json_decode($data, true);
        $page = '';
//        print_r($data);

        if (empty($data['code'])) {
            $plugins = [];
        } else {
            $plugins   = $data['data']['plugins'];
            $paginator = new Bootstrap([], 20, $currentPage, $data['data']['total'], false, ['path' => $this->request->baseUrl()]);
            $page      = $paginator->render();
        }


        $this->assign('plugins', $plugins);


        $this->assign('page', $page);
        return $this->fetch();
    }

    public function installPlugin()
    {
        $id = $this->request->param('id', 0, 'intval');

        $data = cmf_curl_get("https://www.thinkcmf.com/api/appstore/plugins/{$id}?pass=222222222222");

        $data = json_decode($data, true);

        if (empty($data['code'])) {
            $this->error($data['msg']);
        } else {

            $tmpFileName = "plugin{$id}_" . time() . microtime() . '.zip';

            $tmpFileDir = CMF_ROOT . 'data/download/';

            if (!is_dir($tmpFileDir)) {
                mkdir($tmpFileDir, 0777,true);
            }

            $tmpFile = $tmpFileDir . $tmpFileName;
            $fp          = fopen($tmpFile, 'wb') or $this->error('操作失败！'); //新建或打开文件,将curl下载的文件写入文件

            $ch = curl_init($data['data']['plugin']['download_url']);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $res = curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            $archive = new \PclZip($tmpFile);

            $files = $archive->listContent();

            $result = $archive->extract(PCLZIP_OPT_PATH, WEB_ROOT.'plugins/');

        }


        $this->success('安装成功');
    }


}