<?php
class user_model extends model {
    public function __construct()
    {
        parent::__construct();
    }

    public function checkUser($oauth_provider,$id) {
        $query = "SELECT * FROM user WHERE oauth_provider = '".$oauth_provider."' AND oauth_uid = '".$id."'";
        return $this->select($query);
    }

    public function loadListTest($subject_id) {
        $query = "select test.id, test.name, count(manage_test.question_id) as total from subject, unit, test, manage_test
                where manage_test.test_id = test.id
                and test.unit_id = unit.id
                and unit.subject_id = subject.id
                and subject.id = $subject_id
                group by test.id";
        return $this->select($query);
    }

    public function loadDetailOfTest($subject_id) {
        $donesentence = "select e.id, e.name, count(c.id) as donetotal
                    from user as a, do_question as b, question as c, manage_test as d, test as e, unit, subject
                    where a.id = b.user_id
                    and b.question_id = c.id
                    and d.question_id = c.id
                    and e.id = d.test_id
                    and e.unit_id = unit.id
                    and unit.subject_id = subject.id
                    and a.id = " . $_SESSION['id'] . "
                    and subject.id = $subject_id
                    group by e.id";
        return $this->select($donesentence);
    }

    public function checkTestIsClicked($test_id,$user_id) {
        $query = "select * from do_test where test_id = $test_id and user_id = $user_id";
        return $this->select($query);
    }

    public function loadQuestionForDoing($test_id) {
        $query = "select b.indexoftest,a.id,a.name as question,a.a,a.b,a.c,a.d,a.e,a.f,a.answer FROM question as a, manage_test as b, test
            where a.id = b.question_id
            and b.test_id = test.id
            and test.id = $test_id
            and a.id not in (
                select do_question.question_id from do_question
                where user_id = ".$_SESSION['id']."
            )
            order by b.indexoftest asc";
        return $this->select($query);
    }

    public function seeResult($test_id) {
        $query =    "SELECT c.indexoftest,b.id, b.name, b.a, b.b, b.c, b.d, b.e, b.f, a.check, a.answerofuser, b.answer
                    from do_question as a, question as b, manage_test as c, test as d
                    where a.question_id = b.id
                    and c.question_id = b.id
                    and c.test_id = d.id
                    and a.user_id = ".$_SESSION['id']."
                    and d.id = $test_id
                    order by c.indexoftest asc";
        return $this->select($query);
    }
}