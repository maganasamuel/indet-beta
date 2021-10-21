<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require realpath(__DIR__ . '/libs/composer/vendor/autoload.php');
require 'database.php';
include_once 'libs/api/controllers/Adviser.controller.php';

use Coolpraz\PhpBlade\PhpBlade;

class advisers extends Database
{
    public $token;

    public $adviser;

    public $con;

    public function __construct($con)
    {
        parent::__construct();

        if (! isset($_SESSION)) {
            session_start();
        }

        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }

        $this->token = $_SESSION['token'];

        if (! isset($_SESSION['myusername'])) {
            session_destroy();
            header('Refresh:0; url=index.php');
        }

        $this->con = $con;

        $this->adviser = new AdviserController();

        $action = ($_GET['action'] ?? ($_POST['action'] ?? 'index'));

        $this->$action();
    }

    public function index()
    {
        $adviserController = new AdviserController();
        $advisers = $adviserController->getAllAdvisers();

        $blade = new PhpBlade(realpath(__DIR__ . '/views'), realpath(__DIR__ . '/cache'));

        echo $blade->view()->make('advisers.index', [
            'token' => $this->token,
            'con' => $this->con,
            'advisers' => $advisers,
        ]);
    }

    public function listNotes()
    {
        $query = $this->prepare('SELECT * FROM adviser_notes WHERE adviser_id = ? ORDER BY id DESC');

        $query->bind_param('i', $_GET['adviser_id']);

        $query = $this->execute($query);

        $notes = [];

        while ($row = $query->fetch_assoc()) {
            $notes[] = $row;
        }

        echo json_encode($notes);
    }

    public function createNote()
    {
        $query = $this->prepare('INSERT INTO adviser_notes (adviser_id, notes) VALUES (?, ?)');

        $query->bind_param('is', $_POST['adviser_id'], $_POST['notes']);

        $this->execute($query);

        echo json_encode([
            'id' => $this->mysqli->insert_id,
        ]);
    }

    public function updateNote()
    {
        $query = $this->prepare('UPDATE adviser_notes SET notes = ? WHERE id = ?');

        $query->bind_param('si', $_POST['notes'], $_POST['id']);

        $this->execute($query);
    }

    public function deleteNote()
    {
        $query = $this->prepare('DELETE FROM adviser_notes WHERE id = ?');

        $query->bind_param('i', $_POST['id']);

        $this->execute($query);
    }
}

new advisers($con);
