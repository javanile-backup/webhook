<?php
/**
 * Mount command for producer.
 *
 * PHP version 5
 *
 * @category ProducerCommand
 *
 * @author    Francesco Bianco <bianco@javanile.org>
 * @copyright 2015-2017 Javanile.org
 * @license   https://goo.gl/KPZ2qI  MIT License
 */

namespace Javanile\Webhook;

session_start();

class Endpoint extends Manifest
{
    /**
     * @var null
     */
    protected $hook;

    /**
     * @var
     */
    protected $input;

    /**
     * @var
     */
    protected $secret;

    /**
     * @var
     */
    protected $passwd;

    /**
     * @var
     */
    protected $accessLog;

    /**
     * Endpoint constructor.
     *
     * @param null $args
     * @throws \Exception
     */
    public function __construct($args)
    {
        foreach (['manifest', 'request', 'payload'] as $key) {
            if (!isset($args[$key])) {
                throw new \Exception("Argument required '{$key}'.");
            }
        }

        //
        parent::__construct($args['manifest']);

        //
        $this->secret = $args['secret'];
        $this->request = $args['request'];
        $this->payload = $args['payload'];
        $this->client = isset($args['client']) ? $args['client'] : null;
        $this->passwd = isset($args['passwd']) ? $args['passwd'] : null;
        $this->login = isset($args['login']) ? $args['login'] : null;
        $this->hook = isset($args['hook']) ? $args['hook'] : null;
        $this->page = isset($args['page']) ? explode(':', preg_replace('/[^a-z:]/i', '', $args['page'])) : ['task'];
        $this->log = isset($args['log']) ? $args['log'] : 'log';
        $this->accessLog = $this->buildLogger('access');
    }

    /**
     * @return string
     */
    public function run()
    {
        if (!$this->checkManifest()) {
            return $this->error($this->errorManifest);
        }

        if ($this->login == 'passwd'
            && isset($this->secret['webhook_passwd'])
            && $this->secret['webhook_passwd']
        ) if ($this->secret['webhook_passwd'] == $this->passwd) {
            $_SESSION['webhook_sessid'] = md5($this->passwd);
            return header('Location: webhook.php');
        } else {
            return $this->loginForm('Incorrect password.');
        }

        $this->accessLog->info($_SERVER['REQUEST_URI']);

        if ($this->request == 'POST') {
            return $this->runHook();
        }

        if ($this->request == 'GET') {
            if (isset($this->secret['webhook_passwd'])
                && $this->secret['webhook_passwd']
                && (!isset($_SESSION['webhook_sessid'])
                    || (md5($this->secret['webhook_passwd']) != $_SESSION['webhook_sessid']))
            ) {
                return $this->loginForm();
            }

            return $this->runPage();
        }

        http_response_code(400);

        return '<h1>Webhook: Bad request.</h1>';
    }

    /**
     * @return string
     */
    public function runHook()
    {
        //
        if (!$this->hook) {
            return $this->error('Missing hook.');
        }

        //
        $manifest = $this->loadManifest();

        //
        $this->eventLog->info("ackn '{$this->hook}'");

        // Add to ONCE requested task or exec
        foreach ($manifest['hook'][$this->hook] as $key => $value) {
            if ($key == 'task') {
                if (is_array($value)) {
                    foreach ($value as $t) {
                        if (!$t) {
                            continue;
                        }
                        $manifest['once'][] = $this->getTaskExec($t);
                    }
                } else {
                    if ($value) {
                        $manifest['once'][] = $this->getTaskExec($value);
                    }
                }
            } elseif ($key == 'exec') {
                if (is_array($value)) {
                    foreach ($value as $t) {
                        if (!$t) {
                            continue;
                        }
                        $manifest['once'][] = $value;
                    }
                } else {
                    if ($value) {
                        $manifest['once'][] = $value;
                    }
                }
            }
        }

        //
        $manifest['once'] = array_unique($manifest['once']);
        $this->saveManifest($manifest);

        return json_encode($manifest['once']);
    }

    /**
     * Run info panel.
     */
    public function runPage()
    {
        if (!isset($this->page[1]) || !$this->page[1] || $this->page[1] == 'layout') {
            return $this->renderPageLayout();
        } else if ($this->page[1] == 'refresh') {
            return $this->renderPageRefresh();
        }
    }

    /**
     *
     */
    private function renderPageLayout()
    {
        $this->page[1] = 'refresh';
        $refresh = '?page='.implode(':', $this->page);

        $pages = [
            'task'     => 'Task list',
            'access'   => 'Access log',
            'error'    => 'Error log',
            'manifest' => 'Manifest',
        ];

        $nav = '';
        foreach ($pages as $current => $label) {
            $nav .= '<li '.(preg_match('/^'.$current.'/', $this->page[0]) ? 'class="is-active"' : '').'>'
                 . '<a href="?page='.$current.'">'.$label.'</a></li>';
        }
        $nav .= '';

        return $this->render('webhook', '   
            <section class="hero is-link">
                <div class="hero-foot">
                    <div class="container">
                        <nav class="tabs is-boxed">
                            <ul>
                                <li>
                                    <h1 class="title" style="padding: 3px 20px 3px 3px">webhook</h1>
                                </li>
                                '.$nav.'
                            </ul>
                        </nav>
                    </div>
                </div>              
            </section>
                        
            <section class="section" style="padding:20px 0;">
                <div data-refresh="'.$refresh.'" class="container"></div>
            </section>                       
        ');
    }

    /**
     * @return string
     */
    private function renderPageRefresh()
    {
        if ($this->page[0] == 'task') {
            return $this->renderTask();
        }

        return "-";
    }

    /**
     * @return string
     */
    private function renderTask()
    {
        // return task list table
        if (!isset($this->page[2]) || !$this->page[2]) {
            $table = '<table class="table is-striped is-narrow is-fullwidth">'
                . '<thead><tr><th>Name</th></tr></thead><tbody>';
            foreach ($this->listTaskLogs() as $log) {
                $table .= '<tr><td><a href="?page=task:layout:'.$log['name'].'">'.$log['name'].'</a></td></tr>';
            }
            $table .= '</tbody></table>';

            return $table;
        }

        // return single task
        $log = $this->log.'/task/'.$this->page[2].'.log';

        $output = '<pre>';
        $output.= file_get_contents($log);
        $output.= '</pre>';

        return $output;
    }

    /**
     *
     *
     * @param $message
     * @return string
     */
    private function loginForm($message = '')
    {
        return $this->render('webhook | login', '  
            <div class="container has-text-centered">
                <div class="column is-4 is-offset-4">
                    <h3 class="title has-text-grey">webhook</h3>
                    <div class="box">           
                        <form method="POST" style="text-align:center">
                            '.$message.'
                            <div class="field">
                                <div class="control">
                                    <input type="password" class="input" name="passwd" placeholder="Enter password">
                                </div>
                                <input type="hidden" name="login" value="passwd">               
                            </div>                              
                            <input type="submit" class="button is-info" value="Access">                            
                        </form>
                    </div>
                </div>
            </div>
        ');
    }

    /**
     *
     */
    private function render($title, $view)
    {
        $html = '
            <!DOCTYPE html>
            <html>
                <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title>'.$title.'</title>
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.6.1/css/bulma.min.css">
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
                    <script>$(document).ready(function(){$("[data-refresh]").each(function(){var s = $(this);s.load(s.attr("data-refresh"));setInterval(function(){s.load(s.attr("data-refresh"));},5000);})})</script>
                </head>
                <body>'.$view.'</body>
            </html>
        ';

        return trim(preg_replace('~>\s+<~', '><', $html));
    }

    /**
     *
     *
     */
    function listTaskLogs()
    {
        $files = array();
        $dir = $this->log . '/task';

        foreach (scandir($dir) as $file) {
            if ($file[0] == '.') { continue; }
            $info = pathinfo($dir . '/' . $file);
            $stat = stat($dir . '/' . $file);
            $files[] = [
                'name' => $info['filename'],
            ];
        }

        //arsort($files);
        //$files = array_keys($files);

        return $files;
    }

    /**
     *
     *
     */
    private function checkManifest()
    {
        //
        $manifest = $this->loadManifest();
        $host = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];

        //
        if (!isset($manifest['hook']) || !$manifest['hook']) {
            return !$this->errorManifest = 'Manifest without hooks.';
        } elseif (!isset($manifest['host'])) {
            return !$this->errorManifest = 'Undefined "host" value on manifest.';
        } elseif (!$manifest['host']) {
            return !$this->errorManifest = 'Empty "host" value need match with "'.$host.'" on manifest.';
        } elseif ($manifest['host'] != $host) {
            return !$this->errorManifest = 'For security reason "host" value need match with "'.$host.'" on manifest.';
        }

        return true;
    }

    /**
     * @param $message
     * @return string
     */
    protected function error($message)
    {
        return '<h3>Error: '.$message.'</h3>';
    }
}


/*
echo '<h1>Webhook: Informations</h1>';

// loop each hooks
if (is_array($manifest['hook'])) {
    foreach ($manifest['hook'] as $hook => $task) {
        $host = $_SERVER['HTTP_HOST'];
        $path = trim(dirname($_SERVER['REQUEST_URI']), '/');
        $base = "http://{$host}/{$path}";
        $webhook = $base."/webhook.php?hook={$hook}";
        echo '<pre>'.$webhook.'</pre>';
    }
}

//
if (@$manifest['once']) {
    echo '<h2>Pending</h2>';
    foreach ($manifest['once'] as $task) {
        echo '<pre>'.$task.'</pre>';
    }
}

//
$log = $this->basePath.'/logs/'.$this->info.'.log';
if (file_exists($log)) {
    echo '<h2>Log: '.$log.'</h2>';
    echo '<pre>'.file_get_contents($log).'</pre>';
}

//
echo '<style>pre{border:#ccc;background:#eee;padding:5px;margin:0 0 10px 0;}</style>';
echo '<style>h1{margin:0 0 5px 0;}h2{margin:20px 0 5px 0;}</style>';
*/
