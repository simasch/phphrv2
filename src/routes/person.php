<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

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

    if ($person->getId() == null) {
        return $response->withStatus(404)->write('Person not found');
    } else {
        return $this->view->render($response, "person.phtml", ["person" => $person, "message" => '']);
    }
});

$app->post('/person/{id}', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $dao = new PersonDao($this->db);
    $person = $dao->find(filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT));
    $person->setName(filter_var($data['name'], FILTER_SANITIZE_STRING));
    $person = $dao->update($person);

    return $this->view->render($response, "person.phtml", ["person" => $person, "message" => "Person saved"]);
});
