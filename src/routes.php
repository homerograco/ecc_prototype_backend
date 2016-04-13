<?php
// Routes

/**
 * OLD: Reads database of ECC information into an associative array and returns it as json
 */
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

/**
 * Reads database of answers into an associative array and returns it as json
 */
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

/**
 * Reads database of questions into an associative array and returns it as json
 */
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

/**
 * Combines databases of questions and answers into an associative array and returns it as json
 */
$app->get('/questions_answers/[{code}/]', function ($request, $response, $args) {
    // Logging read of ECC codes table
    $this->logger->info("Testing join of questions X answers");
    
    if (isset($args['code'])) {
        $code = $args['code'];
        $question = R::findOne('code_question', 'code=?', array($code));
        $answers = R::find('code_answer', ' code LIKE ? ', [ $code ]);
        $results = array();
        
        foreach($answers as $answer) {
            $results[$question->code][$question->question][] = array($answer->next => $answer->answer);
        }
        
    } else {
        $questions = R::find('code_question');
        $answers = R::find('code_answer');
        $results = array();
    
        foreach($questions as $question) {
            foreach($answers as $answer) {
                if($answer->code == $question->code) {
                    $results[$question->code][$question->question][] = array($answer->next => $answer->answer);
                }
            }
        }
    }

    $json = json_encode(array('results' => $results));
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
