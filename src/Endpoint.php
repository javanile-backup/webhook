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
    protected $cronLog;

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
        $this->info = isset($args['info']) ? preg_replace('/[^a-z]/i', '', $args['info']) : 'event';
        $this->log = isset($args['log']) ? $args['log'] : 'log';
        $this->accessLog = $this->buildLogger('access');
    }

    /**
     * @return string
     */
    public function run()
    {
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

            return $this->runInfo();
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
        if (!isset($manifest['hook']) || !$manifest['hook']) {
            return $this->error('Manifest without hooks.');
        } elseif (!isset($manifest['hook'][$this->hook])) {
            return $this->error("Undefined hook '{$this->hook}'.");
        }

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
    public function runInfo()
    {

        $manifest = $this->loadManifest();

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

        $table = '<table class="table is-stripped is-bordered is-fullwidth"><tr><th></th></tr>';
        foreach ($this->listTaskLogs() as $log) {
            $table .= '<tr><td><a href="'.$log['name'].'">'.$log['name'].'</a></td></tr>';
        }
        $table .= '</table>';

        return $this->render('webhook', '   
            <section class="hero is-link">
                <div class="hero-body" style="padding:10px 0;">
                    <div class="container">                  
                        <h1 class="title">
                            webhook
                        </h1>
                    </div>
                </div>            
                <div class="hero-foot">
                    <div class="container">
                        <nav class="tabs is-boxed">
                            <ul>
                                <li class="is-active">
                                    <a href="?page=home">Overview</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>              
            </section>
                        
            <section class="section" style="padding:20px 0;">
                <div class="container">'.$table.'</div>
            </section>                       
        ');
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
            <section class="is-fullheight">
                <div class="hero-body">
                    <div class="container has-text-centered">
                        <div class="column is-4 is-offset-4">
                            <h3 class="title has-text-grey">webhook</h3>
                            <p class="subtitle has-text-grey">Please login to proceed.</p>
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
                </div>
            </section>
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
     * @param $message
     * @return string
     */
    protected function error($message)
    {
        return json_encode(['error' => $message]);
    }
}
