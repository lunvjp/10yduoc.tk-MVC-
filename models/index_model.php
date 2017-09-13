<?php
class index_model extends model {
    public function __construct() {
        parent::__construct();
    }
    
    public function loadListTest() {
        $query = "select test.id, test.name, count(manage_test.question_id) as total from subject, unit, test, manage_test
                        where manage_test.test_id = test.id
                        and test.unit_id = unit.id
                        and unit.subject_id = subject.id
                        and subject.id = 1
                        group by test.id";
        return $this->select($query);
    }

    public function loadQuestion($id) {
        $query = "select a.id,a.name as question,a.a,a.b,a.c,a.d,a.e,a.f,a.answer FROM question as a, manage_test as b, test
                where a.id = b.question_id
                and b.test_id = test.id
                and test.id = $id";
        return $this->select($query);
    }
}