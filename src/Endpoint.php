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

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
        $this->accessLog = $this->buildLogger('access');
    }

    /**
     * @return string|void
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
        echo '<h1>Webhook: Informations</h1>';

        $manifest = $this->loadManifest();

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
            echo '<h2>Penging</h2>';
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


        return $this->render("   
            <!doctype html>
            <html>
                <head>
                    <link href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' rel='stylesheet'>                    
                </head>            
                <body>       

                    <script>setTimeout(\"window.location.reload()\", 5000);</script>                    
                </body>
            </html>
        ");
    }

    /**
     * @param $message
     * @return string
     */
    private function loginForm($message = '')
    {
        return $this->render("   
            <!doctype html>
            <html>
                <head>
                    <link href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' rel='stylesheet'>
                </head>            
                <body>       
                    <form method='POST' style='text-align:center'>
                        <label>Enter password</label>
                        <input type='password' name='passwd'>
                        <input type='hidden' name='login' value='passwd'>               
                        <input type='submit' value='Access'>
                        {$message}
                    </form>
                </body>
            </html>
        ");
    }

    /**
     *
     */
    private function render($html)
    {
        return trim(preg_replace('~>\s+<~', '><', $html));
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
