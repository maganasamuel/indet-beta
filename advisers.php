<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require realpath(__DIR__ . '/libs/composer/vendor/autoload.php');

use Coolpraz\PhpBlade\PhpBlade;

class advisers
{
    public $token;

    public function __construct()
    {
        session_start();

        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }

        $this->token = $_SESSION['token'];

        if (! isset($_SESSION['myusername'])) {
            session_destroy();
            header('Refresh:0; url=index.php');
        }

        $action = ($_GET['action'] ?? ($_POST['action'] ?? 'index'));

        $this->$action();
    }

    public function index()
    {
        require 'database.php';
        include_once 'libs/api/controllers/Adviser.controller.php';

        $adviserController = new AdviserController();
        $advisers = $adviserController->getAllAdvisers();

        $blade = new PhpBlade(realpath(__DIR__ . '/views'), realpath(__DIR__ . '/cache'));

        echo $blade->view()->make('advisers.index', [
            'token' => $this->token,
            'con' => $con,
            'advisers' => $advisers,
        ]);
    }
}

new advisers();
