<?php
/*
 * Class_Name: user
 * Method:
 * 1. __construct
 * 2. login
 * 3. loadListTest
 * 4. doMoreQuestion
 * 5. logout
 */
class user extends controller {
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('user_model');
        session_start();
    }

    public function login() {
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        //Convert JSON data into PHP variable
        if (isset($_POST['userData'])) $userData = json_decode($_POST['userData']);
        if(!empty($userData)) {

        $_SESSION['username'] = $userData->first_name.' '.$userData->last_name;
        //    $_SESSION['email'] = $userData->email;
        //    $_SESSION['imageUser'] = $userData->picture->data->url;
        if ($userData->email == 'lunvjp@gmail.com') $_SESSION['email'] = 'lunvjp@gmail.com';

        $oauth_provider = $_POST['oauth_provider'];
        //Check whether user data already exists in database
        $prevResult = $this->model->checkUser($oauth_provider,$userData->id);

        $edit['first_name'] = $userData->first_name;
        $edit['last_name'] = $userData->last_name;
        $edit['email'] = $userData->email;
        $edit['gender'] = $userData->gender;
        $edit['locale'] = $userData->locale;
        $edit['picture'] = $userData->picture->data->url;
        $edit['link'] = $userData->link;
        $edit['modified'] = date("Y-m-d H:i:s");

        if($this->model->showRows() > 0){ // Tài khoản đã tồn
            $condition = array('oauth_provider'=>$oauth_provider,'oauth_uid'=>$userData->id);
            $this->model->update($edit,$condition);
        }else{ // Đăng nhập lần đầu tiên vào web
            $edit['oauth_provider'] = $oauth_provider;
            $edit['oauth_uid'] = $userData->id;
            $edit['created'] = date("Y-m-d H:i:s");
            $this->model->insert($edit);
        }

        // Tạo $_SESSION['id'] sau khi nó insert trong database
        $id = $this->model->checkUser($oauth_provider,$userData->id)[0]['id'];
        $_SESSION['id'] = $id;

        $account = "<a href='index.php?controller=user&action=logout'>Đăng xuất</a>";
        $account .= '<a style="pointer-events: none">Chào ' . $_SESSION['username'].'</a>';
        $account .= "<a style='padding:0'><img src=".$userData->picture->data->url."></a>";
        $_SESSION['info'] = $account;
        //    $_SESSION['imageUser'] = $userData->picture->data->url;
        }

        // Khi bất kì thằng nào đăng nhập vào thì cho nó load bài test của môn giải phẫu
        // Khi rồi sau đó nó kích qua môn khác thì trên thành url sẽ có subject id của môn đó
        $this->loadListTest(1); // Bỏ id của subject đó vào đây rồi sau đó nó load dựa vào id của môn đó

        // Trước khi chuyển qua bên kia thì phải load chi tiết các đề đã làm rồi của user
        $this->view->render('dotest/index');
    }

    public function loadListTest($subject_id) {
        if (isset($_POST['done'])) {
            $this->model->table = 'do_question';

            array_shift($_POST);
            $result = $_SESSION['answer'];

            $done = $_POST;
            foreach ($done as $key => $value) {
                $right = $result[$key];
                $check = 0; //Wrong
                if (strtolower($right) == strtolower($value)) { // Right
                    $check = 1;
                }
                $insert = array('user_id' => $_SESSION['id'], 'question_id' => $key, 'check' => $check, 'answerofuser' => $value);
                $this->model->insert($insert, 'single');
            }

//            header('location: .');
//            exit();
        }


        $html = '';
        if (isset($_SESSION['username'])) { // đăng nhập thành công
            // load all of the test in each subject
            $data = $this->model->loadListTest($subject_id);
            $donesentencelist = $this->model->loadDetailOfTest($subject_id);

            foreach ($data as $key => $value) {
                $idsentence = $value['id'];
                $check = false;
                if (!empty($donesentencelist)) {
                    foreach ($donesentencelist as $index => $val) {
                        if ($idsentence == $val['id']) {
                            $html .= '<li>
                            <span><a style="cursor: pointer;" onclick="seeResult(' . $value['id'] . ')">' . $value["name"] . '</a></span> 
                            <span style="padding-left:10px;color: yellowgreen">' . $val['donetotal'] . '/' . $value['total'] . '</span>
                            <span style="padding-left:10px;"><a style="cursor: pointer;color: yellowgreen" onclick="doMoreQuestion(' . $value['id'] . ')">Làm tiếp</a></span>
                        </li>';
                            $check = true;
                            break;
                        }
                    }
                }

                if ($check == false) {
                    // check when user click into 'yes' button but doesn't do any questions
                    $temp = $this->model->checkTestIsClicked($idsentence,$_SESSION['id']);

                    if (!empty($temp))
                    {
                        $check = true;
                        $html .= '<li>
                            <span><a style="cursor: pointer;" onclick="seeResult(' . $value['id'] . ')">' . $value["name"] . '</a></span> 
                            <span style="padding-left:10px;color: yellowgreen">0/' . $value['total'] . '</span>
                            <span style="padding-left:10px;"><a style="cursor: pointer;color: yellowgreen" onclick="doMoreQuestion(' . $value['id'] . ')">Làm tiếp</a></span>
                        </li>';
                    }
                }

                if ($check == false) {
                    $html .= '<li>
                    <span><a style="cursor: pointer" data-toggle="modal" data-target="#myModal" onclick="getLink(' . $value['id'] . ')">' . $value["name"] . '</a></span>
                </li>';

                }
            }

            $this->view->content['listTest'] = $html;
            $this->view->render('dotest/index'); // == require_once ('views/dotest/index.php');
        } else {
            $this->view->render('index');
        }
    }



    public function seeResult() {
        if (isset($_POST['id'])) {
            $detailofdonesentence = $this->model->seeResult($_POST['id']);

            $xhtml = '';
            if (!empty($detailofdonesentence)) {
                $xhtml = '<div class="choiceuser-content" style="width:60%; display: table-cell; vertical-align: top">';

                foreach($detailofdonesentence as $key => $value) {
                    $color = '';
                    $class = '';
                    if ($value['check'] == 0) { // Nếu đáp án sai
                        $color = 'red';
                        $class = 'wrong';
                    } else { // Đáp án đúng
                        $color = 'blue';
                        $class = 'right';
                    }

                    $xhtml .='<div class="question '.$class.'">
                            <div class="item">
                                <p class="title">Câu ' . $value['indexoftest'] . '.</p>
                                <p class="title-content">' . $value['name'] . '</p>
                            </div>';
                    $temp['A'] = $value['a'];
                    $temp['B'] = $value['b'];
                    $temp['C'] = $value['c'];
                    $temp['D'] = $value['d'];
                    if ($value['e']) $temp['E'] = $value['e'];
                    if ($value['f']) $temp['F'] = $value['f'];

                    foreach($temp as $key2 => $value2) {
                        $xhtml .= '<div class="item">
                                <p class="answer">'.$key2.'.</p>
                                <p>' . $value2 . '</p>
                            </div>';
                    }

//                $color = $value['check']==0 ? 'red':'blue';
                    $xhtml .= '<p style="padding-left:10px;display:block;margin:0;font-size:13px;font-family:Arial,sans-serif;line-height:30px;font-weight: 600;height:30px;color: '.$color.';background:#e9ebee">'.strtoupper($value['answer']).' - Trả lời '.strtoupper($value['answerofuser']).'</p>
                        </div>';
                }
                $xhtml .= '</div>';

            }
            $this->view->content['listQuestion'] = $xhtml;
            $this->view->render('dotest/load');
        }
    }

    public function loadFaceComment() {
        if (isset($_POST['id'])) {
            $this->view->content['facebook'] = '<iframe style="width: 100%; height: 100%" src="views/detail/'.$_POST['id'].'.php"></iframe>';
            $this->view->render('dotest/load');
        }
    }

    public function doMoreQuestion($subject_id) {
        if (isset($_POST['id'])) { // Khi người dùng bấm vào làm bài tại đây thì ngay lần đầu tiên chúng ta thêm vào cơ sơ dữ liệu
            $content = '<form action="index.php?controller=user&action=loadListTest&subject_id='.$subject_id.'" method="post" name="form-add" id="form-do-test"><input type="hidden" name="done" value="'.$_POST['id'].'">';
            $id = htmlspecialchars($_POST['id']);
            $id = trim($id);

            // Khi click vào nút yes thì insert id user là $_SESSION['id']
            $this->model->table = 'do_test';
            $insert = array('user_id'=>$_SESSION['id'],'test_id'=>$id);
            $this->model->insert($insert);

            $data = $this->model->loadQuestionForDoing($id);

            $result = array();
            foreach ($data as $key => $value) {
                $result[$value['id']] = $value['answer'];

                $item['A'] = $value['a'];
                $item['B'] = $value['b'];
                $item['C'] = $value['c'];
                $item['D'] = $value['d'];
                if ($value['e']) $item['E'] = $value['e'];
                if ($value['f']) $item['F'] = $value['f'];


                $temp = '<div class="question" id="'.$value['id'].'">
                    <div class="item">
                        <p class="title">Câu ' . $value['indexoftest'] . '.</p>
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
            $_SESSION['answer'] = $result; // mảng kết quả

//            $this->view->content['listQuestion'] = $content;

            // Có 2 trường hợp hàm này được gọi,
            // TH1: Làm đề lần đầu, th này có time trong đó
            // Hồi xưa mình dùng session_time để gọi 2 ajax 1 lần, còn bây giờ ko cần dùng session_time nữa
//            $_SESSION['timeout']=(time() + 30*60) * 1000;
            $this->view->content['time'] = (time() + 30*60) * 1000;
            // TH2: Làm các câu còn lại trong đề
            $doingtest = array('listQuestion' => $content, 'time' => (time() + 30*60) * 1000);
            if (isset($_POST['type']) && $_POST['type'] == 'newTest') $this->view->content['listQuestion'] = json_encode($doingtest);
            else $this->view->content['listQuestion'] = $content;

            // Phải get url subject = giải phẫu luôn để mình
            // Vì tất cả các thư mục làm để là giống nhau nên bản chất chỉ cần lấy tên giải phẫu trên controller sau đó chạy dưới class là ok
            $this->view->render('dotest/load');
        }
    }

    // get Time này để làm để khi bấm nút yes ông ơi
    public function getTime() {
//        if (isset($_POST['time'])) {
//            echo $_SESSION['timeout'];
//        }
        $this->view->content['time'] = $_SESSION['timeout'];
        $this->view->render('dotest/load');
    }




    public function logout() {
        session_unset();
        session_destroy();
        header('location: .');
        exit();
    }
}
