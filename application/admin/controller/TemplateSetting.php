<?php
namespace app\admin\controller;
use think\Db;

class TemplateSetting extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $config = config('maccms.template');
        
        // 获取所有模板目录
        $templates = [];
        $templateDir = './template';
        if (is_dir($templateDir)) {
            $dirs = scandir($templateDir);
            foreach ($dirs as $dir) {
                if ($dir != '.' && $dir != '..' && is_dir($templateDir . '/' . $dir)) {
                    $templates[] = $dir;
                }
            }
        }
        
        $this->assign('config', $config);
        $this->assign('templates', $templates);
        $this->assign('title', '模板设置');
        return $this->fetch('admin@template/setting');
    }

    public function save()
    {
        $param = input('post.');
        
        // 保存模板设置
        $templateConfig = [
            'frontend' => $param['frontend'] ?? [],
            'app' => $param['app'] ?? [],
            'filter' => $param['filter'] ?? [],
            'sort' => $param['sort'] ?? []
        ];
        
        // 写入配置文件
        $configFile = CONF_PATH . 'template.php';
        $configContent = "<?php\nreturn " . var_export($templateConfig, true) . ";\n";
        file_put_contents($configFile, $configContent);
        
        return $this->success('保存成功');
    }

    public function getLang()
    {
        $lang = input('get.lang', 'zh');
        $config = config('maccms.template');
        
        return json([
            'code' => 1,
            'data' => $config['frontend']['lang'][$lang] ?? []
        ]);
    }

    public function preview()
    {
        $template = input('get.template');
        $type = input('get.type', 'frontend');
        
        // 预览模板设置效果
        $config = config('maccms.template');
        
        return json([
            'code' => 1,
            'data' => $config[$type] ?? []
        ]);
    }
}
