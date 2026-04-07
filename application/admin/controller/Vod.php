<?php
namespace app\admin\controller;
use think\Db;

class Vod extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function data()
    {
        $param = input();
        $param['page'] = intval($param['page']) <1 ? 1 : $param['page'];
        $param['limit'] = intval($param['limit']) <1 ? $this->_pagesize : $param['limit'];

        $where = [];
        if(!empty($param['type'])){
            $where['type_id'] = ['eq',$param['type']];
        }
        if(!empty($param['level'])){
            $where['vod_level'] = ['eq',$param['level']];
        }
        if(in_array($param['status'],['0','1'])){
            $where['vod_status'] = ['eq',$param['status']];
        }
        if(in_array($param['copyright'],['0','1'])){
            $where['vod_copyright'] = ['eq',$param['copyright']];
        }
        if(in_array($param['isend'],['0','1'])){
            $where['vod_isend'] = ['eq',$param['isend']];
        }
        if(!empty($param['lock'])){
            $where['vod_lock'] = ['eq',$param['lock']];
        }
        if(!empty($param['state'])){
            $where['vod_state'] = ['eq',$param['state']];
        }
        if(!empty($param['area'])){
            $where['vod_area'] = ['eq',$param['area']];
        }
        if(!empty($param['lang'])){
            $where['vod_lang'] = ['eq',$param['lang']];
        }
        if(in_array($param['plot'],['0','1'])){
            $where['vod_plot'] = ['eq',$param['plot']];
        }

        if(!empty($param['url'])){
            if($param['url']==1){
                $where['vod_play_url'] = '';
            }
        }
        if(!empty($param['points'])){
            $where['vod_points_play|vod_points_down'] = ['gt', 0];
        }
        if(!empty($param['pic'])){
            if($param['pic'] == '1'){
                $where['vod_pic'] = ['eq',''];
            }
            elseif($param['pic'] == '2'){
                $where['vod_pic'] = ['like','http%'];
            }
            elseif($param['pic'] == '3'){
                $where['vod_pic'] = ['like','%#err%'];
            }
        }
        if(!empty($param['weekday'])){
            $where['vod_weekday'] = ['like','%'.$param['weekday'].'%'];
        }
        if(!empty($param['wd'])){
            $param['wd'] = urldecode($param['wd']);
            $where['vod_name|vod_actor'] = ['like','%'.$param['wd'].'%'];
        }
        if(!empty($param['player'])){
            if($param['player']=='no'){
                $where['vod_play_from'] = ['eq',''];
            }
            else {
                $where['vod_play_from'] = ['like', '%' . $param['player'] . '%'];
            }
        }
        if(!empty($param['downer'])){
            if($param['downer']=='no'){
                $where['vod_down_from'] = ['eq',''];
            }
            else {
                $where['vod_down_from'] = ['like', '%' . $param['downer'] . '%'];
            }
        }
        if(!empty($param['server'])){
            $where['vod_play_server|vod_down_server'] = ['like','%'.$param['server'].'%'];
        }
        $order='vod_time desc';
        if(in_array($param['order'],['vod_id','vod_hits','vod_hits_month','vod_hits_week','vod_hits_day'])){
            $order = $param['order'] .' desc';
        }

        if(!empty($param['repeat'])){
            if($param['page'] ==1){
                Db::execute('DROP TABLE IF EXISTS '.config('database.prefix').'tmpvod');
                Db::execute('CREATE TABLE IF NOT EXISTS `'.config('database.prefix').'tmpvod` as (SELECT min(vod_id) as id1,vod_name as name1 FROM '.config('database.prefix').'vod GROUP BY name1 HAVING COUNT(name1)>1)');
            }
            $order='vod_name asc';
            $res = model('Vod')->listRepeatData($where,$order,$param['page'],$param['limit']);
        }
        else{
            $res = model('Vod')->listData($where,$order,$param['page'],$param['limit']);
        }


        foreach($res['list'] as $k=>&$v){
            $v['ismake'] = 1;
            if($GLOBALS['config']['view']['vod_detail'] >0 && $v['vod_time_make'] < $v['vod_time']){
                $v['ismake'] = 0;
            }
        }

        $this->assign('list',$res['list']);
        $this->assign('total',$res['total']);
        $this->assign('page',$res['page']);
        $this->assign('limit',$res['limit']);

        $param['page'] = '{page}';
        $param['limit'] = '{limit}';
        $this->assign('param',$param);

        //分类
        $type_tree = model('Type')->getCache('type_tree');
        $this->assign('type_tree',$type_tree);

        //播放器
        $player_list = config('vodplayer');
        $downer_list = config('voddowner');
        $server_list = config('vodserver');

        $player_list = mac_multisort($player_list,'sort',SORT_DESC,'status','1');
        $downer_list = mac_multisort($downer_list,'sort',SORT_DESC,'status','1');
        $server_list = mac_multisort($server_list,'sort',SORT_DESC,'status','1');


        $this->assign('player_list',$player_list);
        $this->assign('downer_list',$downer_list);
        $this->assign('server_list',$server_list);


        $this->assign('title','视频管理');
        return $this->fetch('admin@vod/index');
    }

    public function batch()
    {
        $param = input();
        if (!empty($param)) {

            mac_echo('<style type="text/css">body{font-size:12px;color: #333333;line-height:21px;}span{font-weight:bold;color:#FF0000}</style>');

            if(empty($param['ck_del']) && empty($param['ck_level']) && empty($param['ck_status']) && empty($param['ck_lock']) && empty($param['ck_hits'])
                && empty($param['ck_points'])
            ){
                return $this->error('没有选择任何参数');
            }


            if($param['ck_del']==2 && empty($param['player'])){
                return $this->error('删除播放组时，必须选择播放器参数');
            }
            if($param['ck_del']==3 && empty($param['downer'])){
                return $this->error('删除下载组时，必须选择下载器参数');
            }

            $where = [];
            if(!empty($param['type'])){
                $where['type_id'] = ['eq',$param['type']];
            }
            if(!empty($param['level'])){
                $where['vod_level'] = ['eq',$param['level']];
            }
            if(in_array($param['status'],['0','1'])){
                $where['vod_status'] = ['eq',$param['status']];
            }
            if(in_array($param['copyright'],['0','1'])){
                $where['vod_copyright'] = ['eq',$param['copyright']];
            }
            if(in_array($param['isend'],['0','1'])){
                $where['vod_isend'] = ['eq',$param['isend']];
            }

            if(!empty($param['lock'])){
                $where['vod_lock'] = ['eq',$param['lock']];
            }
            if(!empty($param['state'])){
                $where['vod_state'] = ['eq',$param['state']];
            }

            if(!empty($param['area'])){
                $where['vod_area'] = ['eq',$param['area']];
            }
            if(!empty($param['lang'])){
                $where['vod_lang'] = ['eq',$param['lang']];
            }

            if(!empty($param['url'])){
                if($param['url']==1){
                    $where['vod_play_url'] = '';
                }
            }
            if(!empty($param['pic'])){
                if($param['pic'] == '1'){
                    $where['vod_pic'] = ['eq',''];
                }
                elseif($param['pic'] == '2'){
                    $where['vod_pic'] = ['like','http%'];
                }
                elseif($param['pic'] == '3'){
                    $where['vod_pic'] = ['like','%#err%'];
                }
            }
            if(!empty($param['wd'])){
                $where['vod_name'] = ['like','%'.$param['wd'].'%'];
            }

            if(!empty($param['weekday'])){
                $where['vod_weekday'] = ['like','%'.$param['weekday'].'%'];
            }
            
            if(!empty($param['player'])){
                if($param['player']=='no'){
                    $where['vod_play_from'] = ['eq',''];
                }
                else {
                    $where['vod_play_from'] = ['like', '%' . $param['player'] . '%'];
                }
            }
            if(!empty($param['downer'])){
                if($param['player']=='no'){
                    $where['vod_down_from'] = ['eq',''];
                }
                else {
                    $where['vod_down_from'] = ['like', '%' . $param['downer'] . '%'];
                }
            }

            if($param['ck_del'] == 1){
                $res = model('Vod')->delData($where);
                mac_echo('批量删除完毕');
                mac_jump( url('vod/batch') ,3);
                exit;
            }


            if(empty($param['page'])){
                $param['page'] = 1;
            }
            if(empty($param['limit'])){
                $param['limit'] = 100;
            }
            if(empty($param['total'])) {
                $param['total'] = model('Vod')->countData($where);
                $param['page_count'] = ceil($param['total'] / $param['limit']);
            }

            if($param['page'] > $param['page_count']) {
                mac_echo('批量操作完毕');
                mac_jump( url('vod/batch') ,3);
                exit;
            }
            mac_echo( "<font color=red>共".$param['total']."条数据需要处理，每页".$param['limit']."条，共".$param['page_count']."页，正在处理第".$param['page']."页数据</font>");

            $order='vod_id desc';
            $res = model('Vod')->listData($where,$order,$param['page'],$param['limit']);

            foreach($res['list'] as  $k=>$v){
                $where2 = [];
                $where2['vod_id'] = $v['vod_id'];

                $update = [];
                $des = $v['vod_id'].','.$v['vod_name'];

                if(!empty($param['ck_level']) && !empty($param['val_level'])){
                    $update['vod_level'] = $param['val_level'];
                    $des .= '&nbsp;推荐值：'.$param['val_level'].'；';
                }
                if(!empty($param['ck_status']) && isset($param['val_status'])){
                    $update['vod_status'] = $param['val_status'];
                    $des .= '&nbsp;状态：'.($param['val_status'] ==1 ? '[已审核]':'[未审核]') .'；';
                }
                if(!empty($param['ck_lock']) && isset($param['val_lock'])){
                    $update['vod_lock'] = $param['val_lock'];
                    $des .= '&nbsp;推荐值：'.($param['val_lock']==1 ? '[锁定]':'[解锁]').'；';
                }
                if(!empty($param['ck_hits']) && $param['val_hits_min']!='' && $param['val_hits_max']!='' ){
                    $update['vod_hits'] = rand($param['val_hits_min'],$param['val_hits_max']);
                    $des .= '&nbsp;人气：'.$update['vod_hits'].'；';
                }
                if(!empty($param['ck_points']) && $param['val_points_play']!=''  ){
                    $update['vod_points_play'] = $param['val_points_play'];
                    $des .= '&nbsp;播放积分：'.$param['val_points_play'].'；';
                }
                if(!empty($param['ck_points']) && $param['val_points_down']!='' ){
                    $update['vod_points_down'] = $param['val_points_down'];
                    $des .= '&nbsp;下载积分：'.$param['val_points_down'].'；';
                }

                if($param['ck_del'] == 2 || $param['ck_del'] ==3){
                    if($param['ck_del']==2) {
                        $pre = 'vod_play';
                        $par = 'player';
                        $des .= '&nbsp;播放组：';
                    }
                    elseif($param['ck_del']==3){
                        $pre = 'vod_down';
                        $par='downer';
                        $des .= '&nbsp;下载组：';
                    }


                    if($param[$par] == $v[$pre.'_from']){
                        $update[$pre.'_from'] = '';
                        $update[$pre.'_server'] = '';
                        $update[$pre.'_note'] = '';
                        $update[$pre.'_url'] = '';
                        $des .= '删除为空；';
                    }
                    else{
                        $vod_from_arr = explode('$$$',$v[$pre.'_from']);
                        $vod_server_arr = explode('$$$',$v[$pre.'_server']);
                        $vod_note_arr = explode('$$$',$v[$pre.'_note']);
                        $vod_url_arr = explode('$$$',$v[$pre.'_url']);

                        $key = array_search($param[$par],$vod_from_arr);
                        if($key!==false){
                            unset($vod_from_arr[$key]);
                            unset($vod_server_arr[$key]);
                            unset($vod_note_arr[$key]);
                            unset($vod_url_arr[$key]);

                            $update[$pre.'_from'] = join('$$$',$vod_from_arr);
                            $update[$pre.'_server'] = join('$$$',$vod_server_arr);
                            $update[$pre.'_note'] = join('$$$',$vod_note_arr);
                            $update[$pre.'_url'] = join('$$$',$vod_url_arr);
                            $des .= '删除；';
                        }
                        else{
                            $des .= '跳过；';
                        }
                    }
                }

                mac_echo($des);
                $res2 = model('Vod')->where($where2)->update($update);

            }
            $param['page']++;
            $url = url('vod/batch') .'?'. http_build_query($param);
            mac_jump( $url ,3);
            exit;
        }

        //分类
        $type_tree = model('Type')->getCache('type_tree');
        $this->assign('type_tree',$type_tree);

        //播放器
        $player_list = config('vodplayer');
        $downer_list = config('voddowner');
        $server_list = config('vodserver');

        $player_list = mac_multisort($player_list,'sort',SORT_DESC,'status','1');
        $downer_list = mac_multisort($downer_list,'sort',SORT_DESC,'status','1');
        $server_list = mac_multisort($server_list,'sort',SORT_DESC,'status','1');


        $this->assign('player_list',$player_list);
        $this->assign('downer_list',$downer_list);
        $this->assign('server_list',$server_list);


        $this->assign('title','视频批量操作');
        return $this->fetch('admin@vod/batch');
    }

    public function info()
    {
        if (request()->isPost()) {
            $param = input('post.');
            $param['vod_content'] = str_replace( $GLOBALS['config']['upload']['protocol'].':','mac:',$param['vod_content']);
            $res = model('Vod')->saveData($param);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }

        $id = input('id');
        $where=[];
        $where['vod_id'] = $id;
        $res = model('Vod')->infoData($where);


        $info = $res['info'] ?? [];
        // 确保播放列表、下载列表和剧情列表存在
        $info['vod_play_list'] = $info['vod_play_list'] ?? [];
        $info['vod_down_list'] = $info['vod_down_list'] ?? [];
        $info['vod_plot_list'] = $info['vod_plot_list'] ?? [];
        $this->assign('info',$info);

        //分类
        try {
            // 直接从数据库查询并生成type_tree，使用tree格式
            $typeModel = new \app\common\model\Type();
            $typeList = $typeModel->listData(['type_mid' => 1],'type_id asc','tree');
            $this->assign('type_tree',$typeList['list']);
        } catch (Exception $e) {
            $this->assign('type_tree',[]);
        }

        //地区、语言
        $config = config('maccms.app');
        $area_list = explode(',',$config['vod_area']);
        $lang_list = explode(',',$config['vod_lang']);
        $this->assign('area_list',$area_list);
        $this->assign('lang_list',$lang_list);

        //用户组
        $group_list = model('Group')->getCache('group_list');
        $this->assign('group_list',$group_list);

        //播放器
        $player_list = config('vodplayer');
        $downer_list = config('voddowner');
        $server_list = config('vodserver');

        $player_list = mac_multisort($player_list,'sort',SORT_DESC,'status','1');
        $downer_list = mac_multisort($downer_list,'sort',SORT_DESC,'status','1');
        $server_list = mac_multisort($server_list,'sort',SORT_DESC,'status','1');

        $this->assign('player_list',$player_list);
        $this->assign('downer_list',$downer_list);
        $this->assign('server_list',$server_list);

        //播放组、下载租
        $this->assign('vod_play_list',$info['vod_play_list']);
        $this->assign('vod_down_list',$info['vod_down_list']);
        $this->assign('vod_plot_list',$info['vod_plot_list']);

        //AI配置
        $ai_configs = model('AiConfig')->where('config_status', 1)->select();
        $this->assign('ai_configs', $ai_configs);

        $this->assign('title','视频信息');
        return $this->fetch('admin@vod/info');
    }

    public function ai_generate()
    {
        if (Request()->isPost()) {
            $param = input('post.');
            $vod_id = $param['vod_id'];
            $ai_config_id = $param['ai_config_id'];
            $generate_type = $param['generate_type'];

            if (empty($vod_id) || empty($ai_config_id) || empty($generate_type)) {
                return $this->error('参数错误');
            }

            $vod_info = model('Vod')->get($vod_id);
            if (!$vod_info) {
                return $this->error('视频不存在');
            }

            $ai_config = model('AiConfig')->get($ai_config_id);
            if (!$ai_config) {
                return $this->error('AI配置不存在');
            }

            // 调用AI服务
            $ai_service = new \app\common\util\AiService($ai_config);
            $result = [];

            switch ($generate_type) {
                case 'intro':
                    $result = $ai_service->generateIntro($vod_info['vod_name'], $vod_info['type_id']);
                    break;
                case 'actors':
                    $result = $ai_service->generateActors($vod_info['vod_name'], $vod_info['vod_actor']);
                    break;
                case 'reviews':
                    $result = $ai_service->generateReviews($vod_info['vod_name']);
                    break;
                case 'episodes':
                    $result = $ai_service->generateEpisodes($vod_info['vod_name']);
                    break;
                case 'all':
                    $result['intro'] = $ai_service->generateIntro($vod_info['vod_name'], $vod_info['type_id']);
                    $result['actors'] = $ai_service->generateActors($vod_info['vod_name'], $vod_info['vod_actor']);
                    $result['reviews'] = $ai_service->generateReviews($vod_info['vod_name']);
                    $result['episodes'] = $ai_service->generateEpisodes($vod_info['vod_name']);
                    break;
                default:
                    return $this->error('生成类型错误');
            }

            // 保存生成结果
            if (isset($result['code']) && $result['code'] == 1) {
                switch ($generate_type) {
                    case 'intro':
                        if (!empty($result['content'])) {
                            model('Vod')->update(['vod_content' => $result['content']], ['vod_id' => $vod_id]);
                        }
                        break;
                    case 'actors':
                        if (!empty($result['actors'])) {
                            // 处理演员信息
                            $actor_names = [];
                            foreach ($result['actors'] as $actor) {
                                $actor_names[] = $actor['name'];
                                // 保存演员信息
                                $actor_data = [
                                    'actor_name' => $actor['name'],
                                    'actor_content' => $actor['bio'],
                                    'actor_pic' => $actor['image'] ?? '',
                                    'actor_time' => time()
                                ];
                                model('Actor')->saveData($actor_data);
                            }
                            model('Vod')->update(['vod_actor' => implode(',', $actor_names)], ['vod_id' => $vod_id]);
                        }
                        break;
                    case 'reviews':
                        if (!empty($result['reviews'])) {
                            foreach ($result['reviews'] as $review) {
                                $art_data = [
                                    'type_id' => 1, // 影评分类
                                    'art_name' => $review['title'],
                                    'art_content' => $review['content'],
                                    'art_rel_vod' => $vod_id,
                                    'art_time' => time()
                                ];
                                model('Art')->saveData($art_data);
                            }
                        }
                        break;
                    case 'episodes':
                        if (!empty($result['episodes'])) {
                            $plots = [];
                            foreach ($result['episodes'] as $episode) {
                                $plots[] = $episode['title'] . '$$$' . $episode['content'];
                            }
                            model('Vod')->update(['vod_plot' => 1, 'vod_plot_list' => implode('$$$', $plots)], ['vod_id' => $vod_id]);
                        }
                        break;
                }
                return $this->success('AI生成成功');
            } else {
                return $this->error('AI生成失败：' . (isset($result['msg']) ? $result['msg'] : '未知错误'));
            }
        }
        return $this->error('非法访问');
    }

    public function del()
    {
        $param = input();
        $ids = $param['ids'];

        if(!empty($ids)){
            $where=[];
            $where['vod_id'] = ['in',$ids];
            $res = model('Vod')->delData($where);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        elseif(!empty($param['repeat'])){
            $st = ' not in ';
            if($param['retain']=='max'){
                $st=' in ';
            }
            $sql = 'delete from '.config('database.prefix').'vod where vod_name in(select name1 from '.config('database.prefix').'tmpvod) and vod_id '.$st.'(select id1 from '.config('database.prefix').'tmpvod)';
            $res = model('Vod')->execute($sql);
            if($res===false){
                return $this->success('删除失败');
            }
            return $this->success('删除成功');
        }
        return $this->error('参数错误');
    }

    public function field()
    {
        $param = input();
        $ids = $param['ids'];
        $col = $param['col'];
        $val = $param['val'];
        $start = $param['start'];
        $end = $param['end'];


        if(!empty($ids) && in_array($col,['vod_status','vod_lock','vod_level','vod_hits','type_id'])){
            $where=[];
            $where['vod_id'] = ['in',$ids];
            $update = [];
            if(empty($start)) {
                $update[$col] = $val;
                if($col == 'type_id'){
                    $type_list = model('Type')->getCache();
                    $id1 = intval($type_list[$val]['type_pid']);
                    $update['type_id_1'] = $id1;
                }
                $res = model('Vod')->fieldData($where, $update);
            }
            else{
                if(empty($end)){$end = 9999;}
                $ids = explode(',',$ids);
                foreach($ids as $k=>$v){
                    $val = rand($start,$end);
                    $where['vod_id'] = ['eq',$v];
                    $update[$col] = $val;
                    $res = model('Vod')->fieldData($where, $update);
                }
            }
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error('参数错误');
    }

    public function updateToday()
    {
        $param = input();
        $flag = $param['flag'];
        $res = model('Vod')->updateToday($flag);
        return json($res);
    }

}
