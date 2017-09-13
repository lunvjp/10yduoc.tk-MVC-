<?php
class index extends controller {
    public function __construct() {
        parent::__construct();
        $this->loadModel('index_model');
        session_start();
    }

    public function index() {
        if (isset($_POST['done'])) {
            array_shift($_POST);
            $result = $_SESSION['answer'];

            $done = $_POST;

            $dem = 0;
            foreach ($done as $key => $value) {
                $right = $result[$key];
                if (strtolower($right) == strtolower($value)) {
                    $dem++;
                }
            }

            $_SESSION['result'] = "<div style='position: relative;' class='alert alert-success alert-dismissable fade in'>
                        <a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>
                        <span style='width:100%;'>Kết quả: " . $dem . "/20</span>
                        <span style='width:100%;'>Đăng nhập để xem chi tiết đáp án và làm thêm thật nhiều đề nhé</span>
                    </div>";
            header('location: .');
            exit();
        }

        $this->loadListTest();
        $this->loadQuestion();

        $this->view->render('index');
    }
    
    public function loadListTest() {
        // Thao tác với model ở đây
        $data = $this->model->loadListTest();
            
        $html = '';
        foreach ($data as $key => $value) {
            if ($key <= 4) {
                $html .= '<li><span><a style="cursor: pointer;color: yellowgreen" onclick="doTest(' . $value['id'] . ')">' . $value["name"] . '</a></span></li>';
            } else {
                $html .= '<li><span><a style="cursor: pointer;color: red;" data-toggle="modal" data-target="#myModal">' . $value["name"] . '</a></span></li>';
            }
        }
        
        // Thao tác với view ở đây
        $this->view->content['listTest'] = $html;
    }

    public function loadQuestion() {
        // Thao tác với model ở đây
        $data = $this->model->loadQuestion(1);

        $result = array(); // Biến này dùng để tạo session luôn 
        $content = '<form method="post" name="form-add" id="form-do-test"><input type="hidden" name="done" value="1">';
        foreach ($data as $key => $value) {
            if ($key == 20) break;
            $result[$value['id']] = $value['answer'];

            $item['A'] = $value['a'];
            $item['B'] = $value['b'];
            $item['C'] = $value['c'];
            $item['D'] = $value['d'];
            if ($value['e']) $item['E'] = $value['e'];
            if ($value['f']) $item['F'] = $value['f'];


            $temp = '<div class="question" id="'.$value['id'].'">
                            <div class="item">
                                <p class="title">Câu ' . ($key+1) . '.</p>
                                <p class="title-content">' . $value['question'] . '</p>
                            </div>';
            foreach($item as $i => $val) {
                $temp .= '<div class="item">
                                <p class="answer">'.$i.'.</p>
                                <p style="width:2%;vertical-align: middle;"><input class="'.$value['id'].'" value="'.strtolower($i).'" type="radio" name="'.$value['id'].'"></p>
                                <p style="padding-left:10px;"><span>'.$val.'</span></p>
                            </div>';
            }
            $temp .='<hr></div>';
            $content .=$temp;

        }
        $content .='</form>';
        
        // Thao tác với view ở đây
        $this->view->content['listQuestion'] = $content;
        $this->view->content['answer'] = $result; // Lần đầu liên load câu hỏi ở đây
    }

    public function doTest() {
        // Ajax load question on the right when user click each line on the left site
        if (isset($_POST['id'])) {
            $content = '<form method="post" name="form-add" id="form-do-test"><input type="hidden" name="done" value="'.$_POST['id'].'">';
            $id = htmlspecialchars($_POST['id']);
            $id = trim($id);

            $data = $this->model->loadQuestion($id);

            $result = array();
            foreach ($data as $key => $value) {
                if ($key == 20) break;
                $result[$value['id']] = $value['answer'];

                $item['A'] = $value['a'];
                $item['B'] = $value['b'];
                $item['C'] = $value['c'];
                $item['D'] = $value['d'];
                if ($value['e']) $item['E'] = $value['e'];
                if ($value['f']) $item['F'] = $value['f'];


                $temp = '<div class="question" id="'.$value['id'].'">
                    <div class="item">
                        <p class="title">Câu ' . ($key+1) . '.</p>
                        <p class="title-content">' . $value['question'] . '</p>
                    </div>';
                foreach($item as $i => $val) {
                    $temp .= '<div class="item">
                        <p class="answer">'.$i.'.</p>
                        <p style="width:2%;vertical-align: middle;"><input class="'.$value['id'].'" value="'.strtolower($i).'" type="radio" name="'.$value['id'].'"></p>
                        <p style="padding-left:10px;"><span>'.$val.'</span></p>
                    </div>';
                }
                $temp .='<hr></div>';
                $content .=$temp;

            }
            $content .='</form>';
            $this->view->content['listQuestion'] = $content;

//          echo $content;
            $_SESSION['answer'] = $result; // mảng kết quả
//          $_SESSION['timeout']=(time() + 30*60) * 1000;

            $this->view->render('load');
        }
    }
}
?>
