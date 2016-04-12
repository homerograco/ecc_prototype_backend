<?php
// Routes

/*
$app->get('/all/', function ($request, $response) {
    // Logging read of ECC codes table
    $this->logger->info("Reading whole table of ECC codes");
    
    $table = R::find('ecc_codes');
    $json = json_encode(array('results' => R::exportAll($table)));
    $newResponse = $response->withHeader('Content-type', 'application/json');
    //$newResponse->getBody()->write(var_export($json, true));
    $newResponse->getBody()->write($json);
    return $newResponse;
});
 * 
 */

//More general GET request
$app->get('/all/[{id}/]', function ($request, $response, $args) {

    $this->logger->info("Reading ECC codes");
    
    if (isset($args['id'])) {
        $ecc_id = $args['id'];
        $table = R::findOne('ecc_codes', 'id=?', array($ecc_id));
    } else {
        $table = R::find('ecc_codes');
    }
    
    $json = json_encode(array('results' => R::exportAll($table)));
    $newResponse = $response->withHeader('Content-type', 'application/json');
    $newResponse->getBody()->write($json);
    return $newResponse;
});

$app->get('/answers/[{id}/]', function ($request, $response, $args) {
    
    $this->logger->info("Reading codes-answers table");
    
    if (isset($args['id'])) {
        $ecc_id = $args['id'];
        $table = R::findOne('code_answer', 'id=?', array($ecc_id));
    } else {
        $table = R::find('code_answer');
    }
    
    $json = json_encode(array('results' => R::exportAll($table)));
    $newResponse = $response->withHeader('Content-type', 'application/json');
    $newResponse->getBody()->write($json);
    return $newResponse;
});

$app->get('/questions/[{id}/]', function ($request, $response, $args) {
    // Logging read of ECC codes table
    $this->logger->info("Reading codes-questions table");
    
    if (isset($args['id'])) {
        $ecc_id = $args['id'];
        $table = R::findOne('code_question', 'id=?', array($ecc_id));
    } else {
        $table = R::find('code_question');
    }
    
    $json = json_encode(array('results' => R::exportAll($table)));
    $newResponse = $response->withHeader('Content-type', 'application/json');
    $newResponse->getBody()->write($json);
    return $newResponse;
});

$app->put('/all/{id}', function ($request, $response, $args) {
    // Logging update of ECC codes table
    $this->logger->info("Updating ECC code by id");
    
    $input = $request->getParsedBody();
        
    $ecc_id = $args['id'];
    
    $table = R::findOne('ecc_codes', 'id=?', array($ecc_id));
    $table->code_question = $input['code_question'];
    R::store($table);
});
