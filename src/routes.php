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
    //$test = array('results' => R::exportAll($table));
    //print_r($test);

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
$app->get('/questions_answers/[{id}/]', function ($request, $response, $args) {
    // Logging read of ECC codes table
    $this->logger->info("Reading DBs of questions and answers into assoc. array and returning as JSON");
    
    if (isset($args['id'])) {
        $id = $args['id'];
        $question = R::findOne('code_question', 'id=?', array($id));
        $code = $question->code;
        $answers = R::find('code_answer', ' code=? ', array($code));
        $results = array();
        $array_pos = 0;
        
        foreach($answers as $answer) {
            $results[$array_pos]['question_id'] = $question->id;
            $results[$array_pos]['question_code'] = $question->code;
            $results[$array_pos]['question'] = $question->question;
            $results[$array_pos]['code_next'] = $answer->next;
            $results[$array_pos]['answer_id'] = $answer->id;
            $results[$array_pos]['answer'] = $answer->answer;
            $array_pos++;
        }
        
    } else {
        $questions = R::find('code_question');
        $answers = R::find('code_answer');
        $results = array();
        $array_pos = 0;
    
        foreach($questions as $question) {
            foreach($answers as $answer) {
                if($answer->code == $question->code) {
                    $results[$array_pos]['question_id'] = $question->id;
                    $results[$array_pos]['question_code'] = $question->code;
                    $results[$array_pos]['question'] = $question->question;
                    $results[$array_pos]['code_next'] = $answer->next;
                    $results[$array_pos]['answer_id'] = $answer->id;
                    $results[$array_pos]['answer'] = $answer->answer;
                    $array_pos++;
                }
            }
        }
    }

    //$test = array('results' => $results);
    //print_r($test);

    $json = json_encode(array('results' => $results));
    $newResponse = $response->withHeader('Content-type', 'application/json');
    $newResponse->getBody()->write($json);
    return $newResponse;
});

/**
 * OLD: Updates code question on old database, looking for its ID.
 */
$app->put('/all/{id}', function ($request, $response, $args) {
    // Logging update of ECC codes table
    $this->logger->info("Updating ECC code by id");
    
    $input = $request->getParsedBody();
        
    $ecc_id = $args['id'];
    
    $table = R::findOne('ecc_codes', 'id=?', array($ecc_id));
    $table->code_question = $input['code_question'];
    R::store($table);
});

/*
 * 
 */
$app->put('/questions_answers/[{id}/]', function ($request, $response, $args) {
    // Logging update of ECC codes table
    $this->logger->info("Updating ECC info by code");
    
    $input = $request->getParsedBody();
        
    $id = $args['id'];
    
    //Update current answer related code
    $answer = R::find('code_answer', 'id=?', array($id));
    $original_code = $answer->code;
    $answer->code = $input['code_question'];
    
    //Create a copy of the current question, updating the code and text from imput
    $original_question = R::findOne('code_question', 'code=?', array($original_code));
    $question = R::dispense('code_question');
    $question->code = $input['code_question'];
    $question->question = $input['question'];
    $question->help = $original_question->help;
    $question->comment = $original_question->comment;
    
    R::store($question);
    R::store($answer);
});