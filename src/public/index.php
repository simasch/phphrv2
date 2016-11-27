<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

spl_autoload_register(function ($classname) {
    require("../classes/" . $classname . ".php");
});

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host'] = "localhost";
$config['db']['user'] = "root";
$config['db']['pass'] = "";
$config['db']['dbname'] = "hr";

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['view'] = new \Slim\Views\PhpRenderer("../templates/");

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $this->logger->addInfo("Something interesting happened");

    $name = $request->getAttribute('name');

    $stmt = $this->db->prepare("SELECT id, name FROM person WHERE id = 1");
    $stmt->execute();

    $row = $stmt->fetch();
    $this->logger->addInfo($row["id"] . $row["name"]);

    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->get('/person', function (Request $request, Response $response) {
    $this->logger->addInfo("People list");
    $dao = new PersonDao($this->db);
    $people = $dao->listPeople();

    return $this->view->render($response, "people.phtml", ["people" => $people]);
});

$app->get('/person/{id}', function (Request $request, Response $response, $args) {
    $id = (int)$args['id'];
    $dao = new PersonDao($this->db);
    $person = $dao->find($id);

    return $this->view->render($response, "person.phtml", ["person" => $person]);
});

$app->post('/person/{id}', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $dao = new PersonDao($this->db);
    $person = $dao->find(filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT));
    $person->setName(filter_var($data['name'], FILTER_SANITIZE_STRING));
    $person = $dao->update($person);

    return $this->view->render($response, "person.phtml", ["person" => $person, "message" => "Person saved"]);
});

$app->run();