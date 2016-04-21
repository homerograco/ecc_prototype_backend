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
        $table = R::findOne('answers', 'id=?', array($ecc_id));
    } else {
        $table = R::find('answers');
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
        $table = R::findOne('questions', 'id=?', array($ecc_id));
    } else {
        $table = R::find('questions');
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
        $question = R::findOne('questions', 'id=?', array($id));
        $code = $question->code;
        $answers = R::find('answers', ' code=? ', array($code));
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
        $questions = R::find('questions');
        $answers = R::find('answers');
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
    $table->questions = $input['questions'];
    R::store($table);
});

/**
 * Stores updates on a question entry
 */
$app->put('/questions/[{id}]', function ($request, $response, $args) {
    // Logging update of ECC codes table
    $this->logger->info("Storing updates in question {id}");
    
    $input = $request->getParsedBody();
        
    $question_id = $args['id'];
    
    $table = R::findOne('questions', 'id=?', array($question_id));
    $table->code = $input['code'];
    $table->question = $input['question'];
    $table->help = $input['help'];
    $table->comment = $input['comment'];
    
    R::store($table);
});

/**
 * Stores updates on an answer entry
 */
$app->put('/answers/[{id}]', function ($request, $response, $args) {
    // Logging update of ECC codes table
    $this->logger->info("Storing updates in answer {id}");
    
    $input = $request->getParsedBody();
        
    $answer_id = $args['id'];
    
    $table = R::findOne('answers', 'id=?', array($answer_id));
    $table->code = $input['code'];
    $table->answer = $input['answer'];
    $table->next = $input['next'];
    
    R::store($table);
});

/**
 * Inserts questions into the database
 */
$app->post('/questions/[{id}]', function ($request, $response, $args) {
    // Logging insertion into questions table
    $this->logger->info("Inserting new question");
    
    $input = $request->getParsedBody();
    $question = R::dispense('questions'); //dispense only works with char-lowercase based names, no special simbols, no uppercase, no numbers!
    
    $question->code = $input['code'];
    $question->question = $input['question'];
    $question->help = $input['help'];
    $question->comment = $input['comment'];
    
    $id = R::store($question);
    
    $newResponse = $response->withHeader('Content-type', 'application/text');
    $newResponse->getBody()->write('The database returned the following ID: '.$id);
    return $newResponse;
});

/**
 * Inserts answers into the database
 */
$app->post('/answers/[{id}]', function ($request, $response, $args) {
    // Logging insertion into answers table
    $this->logger->info("Inserting new answer");
    
    $input = $request->getParsedBody();
    $answer = R::dispense('answers'); //dispense only works with char-lowercase based names, no special simbols, no uppercase, no numbers!
    
    $answer->code = $input['code'];
    $answer->next = $input['next'];
    $answer->answer = $input['answer'];
    
    $id = R::store($answer);
    
    $newResponse = $response->withHeader('Content-type', 'application/text');
    $newResponse->getBody()->write('The database returned the following ID: '.$id);
    return $newResponse;
});

/**
 * Deletes questions from the database
 */
$app->delete('/questions/{id}', function ($request, $response, $args) {
    // Logging deletion from questions table
    $this->logger->info("Deleting a question {id}");
        
    $question_id = $args['id'];
    
    $question = R::findOne('questions', 'id=?', array($question_id));
    R::trash($question);
});

/**
 * Deletes answers from the database
 */
$app->delete('/answers/{id}', function ($request, $response, $args) {
    // Logging deletion from answers table
    $this->logger->info("Deleting an answer {id}");
        
    $answer_id = $args['id'];
    
    $answer = R::findOne('answers', 'id=?', array($answer_id));
    R::trash($answer);
});