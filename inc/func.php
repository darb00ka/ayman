<?php
require 'dbh.inc.php';
date_default_timezone_set("Africa/Cairo");

// تضمين الملف الجديد لمعالجة الموقع
require_once 'location_updater.php';

function safe($data, $type)
{
    switch ($type) {
        case 'string':
            $data = filter_var($data, FILTER_SANITIZE_STRING);
            break;
        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            break;
        case 'mail':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            break;
    }
    $data = makesafe($data);
    return $data;
}
function emptyRow($cells, $caption)
{
?>
    <tr>
        <td colspan="<?php echo $cells; ?>" class="text-center">
            <?php echo $caption ?>
        </td>
    </tr>
<?php
}
function makefixArea($data)
{
    $ent = "\n\r";
    $data = str_replace('<br/>', $ent, $data);
    $data = str_replace('\r\n', $ent, $data);
    $data = str_replace('"', '&amp;#34;', $data);
    return $data;
}
function sendError($err)
{
?>
    <div class="alert alert-danger">
        <?php echo $err; ?>
    </div>
<?php
}
function sendSucc($succ)
{
?>
    <div class="alert alert-success mt-3 mb-3">
        <?php echo $succ; ?>
    </div>
<?php
}
function dayDate($time)
{
    $data = date('j M Y', $time);
    return $data;
}
function arabicDate($time)
{
    $data = date('j M Y', $time);
    $date = explode(' ', $data);
    $month = $date[1];
    switch ($month) {
        case 'Jan':
            $month = "يناير";
            break;
        case 'Feb':
            $month = "فبراير";
            break;
        case 'Mar':
            $month = "مارس";
            break;
        case 'Apr':
            $month = "إبريل";
            break;
        case 'May':
            $month = "مايو";
            break;
        case 'Jun':
            $month = "يونيو";
            break;
        case 'Jul':
            $month = "يوليو";
            break;
        case 'Aug':
            $month = "أغسطس";
            break;
        case 'Sep':
            $month = "سبتمبر";
            break;
        case 'Oct':
            $month = "أكتوبر";
            break;
        case 'Nov':
            $month = "نوفمبر";
            break;
        case 'Dec':
            $month = "ديسمبر";
            break;
    }
    $data = $date[0] . " " . $month . " " . $date[2];
    return $data;
}
function timeHour($time)
{
    $data = date('g:i a', $time);
    $data = explode(' ', $data);
    if ($data[1] == "am") {
        $prefx = "ص";
    } else {
        $prefx = "م";
    }
    $data = $data[0] . ' ' . $prefx;
    return $data;
}
function makesafe($data)
{
    $data = strip_tags($data);
    $data = htmlspecialchars($data);
    $data = addslashes($data);
    return $data;
}
function makefix($data)
{
    $data = str_replace('\n\r', '<br/>', $data);
    $data = str_replace("&#39;", "'", $data);
    $data = str_replace('\r\n', '<br/>', $data);
    $data = str_replace('\n', '&&&', $data);
    $data = str_replace('\r', '&&&', $data);
    $data = str_replace('\&&&\&&&', '<br/>', $data);
    $data = str_replace('&&&', '<br/>', $data);
    return $data;
}
function makeFixDesc($data)
{
    $data = str_replace('\n\r', ' ', $data);
    $data = str_replace("&#39;", "'", $data);
    $data = str_replace('\r\n', ' ', $data);
    $data = str_replace('\n', '&&&', $data);
    $data = str_replace('\r', '&&&', $data);
    $data = str_replace('\&&&\&&&', ' ', $data);
    $data = str_replace('&&&', ' ', $data);
    return $data;
}
function getLimitLetters($data, $limit)
{
    $data = str_split($data);
    $final = '';
    for ($i = 0; $i < $limit; $i++) {
        $final .= $data[$i];
    }
    if (count($data) - 1 > $i) {
        $final .= '<b>.... اضغط أكمل القراءة لاستكمال قراءة المقال</b>';
    }
    return $final;
}

// **تم تعديل دالة signup**
function signup($conn, $first_name, $last_name, $email, $phone, $pwd)
{
    $back = array("error" => false);
    $pwd = password_hash($pwd, PASSWORD_DEFAULT);
    $account_status = "Waiting";
    
    // ملاحظة: لا نمرر IP/User-Agent هنا إلى جدول accounts، بل نعتمد على جدول user_locations فقط.
    // لكن دالة update_user_location تتطلبها، لذا سنستمر في جلبها هنا.
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // استعلام INSERT الأصلي بدون حقول IP/User-Agent في جدول accounts
    $sql = "INSERT INTO accounts (first_name, last_name, email, phone, pwd, account_status) VALUES (?,?,?,?,?,?)";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #001 " . $stmt->error;
    } else {
        mysqli_stmt_bind_param($stmt, 'ssssss', $first_name, $last_name, $email, $phone, $pwd, $account_status);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #002";
        } else {
            $get_id = last_id($conn, 'accounts');
            if ($get_id['error']) {
                $back['error'] = true;
                $back['message'] = $get_id['message'];
            } else {
                $user_id = $get_id['id'];
                $back['id'] = $user_id;

                // تحديث الموقع في جدول user_locations
                // عند التسجيل، لا نملك الموقع الدقيق من المتصفح بعد، لذا نعتمد على الـ IP فقط.
                update_user_location($conn, $user_id, $user_ip, $user_agent);

                $admins = admins($conn);
                $admins = $admins['admins'];
                foreach ($admins as $admin) {
                    send_noti($conn, $admin, "هناك حساب جديد في إنتظار موافقتك", 'admin_accounts');
                }
            }
        }
    }
    return $back;
}

// **تم تعديل دالة login**
function login($conn, $email, $pwd, $remember)
{
    $back = array('error' => false);
    $sql = "SELECT * FROM accounts WHERE email=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) :
        $back['error'] = true;
        $back['message'] = "خطأ تقني #5، يرجى المحاولة لاحقا";
    else :
        mysqli_stmt_bind_param($stmt, 's', $email);
        if (!mysqli_stmt_execute($stmt)) :
            $back['error'] = true;
            $back['message'] = "خطأ تقني #6، يرجى المحاولة لاحقا";
        else :
            $result = mysqli_stmt_get_result($stmt);
            $resultCheck = mysqli_num_rows($result);
            if ($resultCheck === 0) :
                $back['error'] = true;
                $back['message'] =  "هذا الحساب غير موجود";
            else :
                $member = mysqli_fetch_assoc($result);
                $hashedPwdCheck = password_verify($pwd, $member['pwd']);
                if ($hashedPwdCheck == false) :
                    $back['error'] = true;
                    $back['message'] =  "كلمة السر خطأ";
                else :
                    // session_start(); // تأكد أن session_start() تم استدعاؤها في بداية الملف أو قبله
                    $_SESSION['id'] = $member['id'];
                    $_SESSION['first_name'] = $member['first_name'];
                    $_SESSION['last_name'] = $member['last_name'];
                    $_SESSION['email'] = $member['email'];
                    $_SESSION['phone'] = $member['phone'];
                    $_SESSION['nid'] = $member['nid'];
                    $_SESSION['account_status'] = $member['account_status'];
                    $_SESSION['acc_type'] = $member['acc_type'];
                    $_SESSION['verified'] = $member['verified'];
                    // ملاحظة: لا تعين $_SESSION['admin'] = 1 هنا، بل اعتمد على $member['acc_type'] == "admin" لتحديد الصلاحية
                    // وهذا ما يتم بالفعل في السكربت الأصلي للموقع لتحديد توجيه الـ URL

                    // تحديث الـ IP والـ User-Agent واللوكيشن في جدول user_locations
                    $user_ip = $_SERVER['REMOTE_ADDR'] ?? null;
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
                    update_user_location($conn, $member['id'], $user_ip, $user_agent);

                    if ($remember == "remember") {
                        setcookie('id', $member['id'], time() + 2952000, '/', 'wastetco.com');
                        setcookie('first_name', $member['first_name'], time() + 2952000, '/', 'wastetco.com');
                        setcookie('last_name', $member['last_name'], time() + 2952000, '/', 'wastetco.com');
                        setcookie('email', $member['email'], time() + 2952000, '/', 'wastetco.com');
                        setcookie('phone', $member['phone'], time() + 2952000, '/', 'wastetco.com');
                        setcookie('nid', $member['nid'], time() + 2952000, '/', 'wastetco.com');
                        setcookie('account_status', $member['account_status'], time() + 2952000, '/', 'wastetco.com');
                        setcookie('acc_type', $member['acc_type'], time() + 2952000, '/', 'wastetco.com');
                        setcookie('verified', $member['verified'], time() + 2952000, '/', 'wastetco.com');
                        // لا تعين الكوكي 'admin' = 1 هنا، فقد يكون المستخدم ليس مسؤولًا
                        // setcookie('admin', 1, time() + 2952000, '/', 'wastetco.com');
                    }
                    if ($member['acc_type'] == "admin") { // استخدم $member['acc_type'] مباشرة
                        $back['url'] = "pages/admin";
                    } else {
                        $back['url'] = "pages/index";
                    }
                endif;
            endif;
        endif;
    endif;
    return $back;
}

function last_id($conn, $table)
{
    $back = array("error" => false);
    $sql = "SELECT id FROM $table ORDER BY id DESC LIMIT 1";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #003";
    } else {
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #004";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $back['id'] = $row['id'];
        }
    }
    return $back;
}

function id_card_upload($id, $file)
{
    $back = array("error" => false);
    $name = $file['name'];
    $type = $file['type'];
    $tmp = $file['tmp_name'];
    $file_error = $file['error'];
    $size = $file['size'];
    $ext = explode('.', $name);
    $ext = strtolower(end($ext));
    $allowed_ext = array('png', 'jpg', 'jpeg');
    if (!in_array($ext, $allowed_ext)) {
        $back['error'] = true;
        $back['message'] = "صيغة الملف المرفوع غير مسموح بها، الرجاء إختيار ملف عبارة عن صورة للبطاقة";
    } else {
        $new_name = $id . ".jpg";
        if ($size > 10000000) {
            $back['error'] = true;
            $back['message'] = "حجم الصورة أكبر من المسموح به، يرجى رفع صورة بحجم أصغر من 10 ميجا بايت";
        } else {
            if (!move_uploaded_file($tmp, 'attachments/id_cards/' . $new_name)) {
                $back['error'] = true;
                $back['message'] = "لم نستطع تحميل صورة البطاقة الخاصة بك، يرجى المحاولة مجدداً";
            }
        }
    }
    return $back;
}

function print_noti($type, $content)
{
?>
    <div class="alert alert-<?php echo $type ?>"><?php echo $content ?></div>
<?php
}

function check_email($conn, $email)
{
    $back = array("error" => false);
    $sql = "SELECT email FROM accounts WHERE email=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #005";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $email);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #006";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            if ($check > 0) {
                $back['check'] = true;
            } else {
                $back['check'] = false;
            }
        }
    }
    return $back;
}

function update_phone($conn, $phone)
{
    $back = array("error" => false);
    $sql = "UPDATE accounts SET phone=? WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #007";
    } else {
        mysqli_stmt_bind_param($stmt, 'ss', $phone, $_SESSION['id']);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #008";
        } else {
            $back['check'] = "success";
        }
    }
    return $back;
}

function send_noti($conn, $to, $title, $url)
{
    $back = array("error" => false);
    $sql = "INSERT INTO noti (noti_to, noti_title, noti_url, noti_timestamp) VALUES (?,?,?,?)";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #009";
    } else {
        $now = time();
        mysqli_stmt_bind_param($stmt, 'ssss', $to, $title, $url, $now);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #010، " . $stmt->error;
        } else {
            $user = is_user($conn, $to);
            if($user['error']){
                $back = $user;
            }else{
                $user = $user['user'];
                $from = "support@wastetco.com";
                $headers = "From:" . $from;
                mail($user['email'], $title, $headers);
            }
        }
    }
    return $back;
}

function get_noti($conn, $limit)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM noti WHERE noti_to=? ORDER BY id DESC LIMIT $limit"; 
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #011";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $_SESSION['id']);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #012";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] = $num;
            if ($num > 0) {
                $notis = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $notis[] = $row;
                }
                $back['notis'] = $notis;
            }
        }
    }
    return $back;
}

function add_request($conn, $process_code)
{
    $back = array("error" => false);
    $type = "add";
    $now = time();
    $sql = "INSERT INTO finance_requests (user_id, type, process_code, request_timestamp) VALUES (?,?,?,?)";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #013";
    } else {
        $now = time();
        mysqli_stmt_bind_param($stmt, 'ssss', $_SESSION['id'], $type, $process_code, $now);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #014";
        } else {
            $back['check'] = "success";
            $admins = admins($conn);
            $admins = $admins['admins'];
            foreach ($admins as $admin) {
                send_noti($conn, $admin, 'يوجد طلب إضافة رصيد جديد من أحد العملاء، إضغط هنا للإنتقال لصفحة إدارة الرصيد', 'balance');
            }
        }
    }
    return $back;
}

function last_process_id($conn, $user)
{
    $back = array("error" => false);
    $sql = "SELECT id FROM finance_process WHERE user_id=? ORDER BY id DESC LIMIT 1";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #019";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $user);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #020";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $back['id'] = $row['id'];
        }
    }
    return $back;
}

function withdraw_request($conn, $value)
{
    $back = array("error" => false);
    $type = "withdraw";
    $now = time();
    $sql = "INSERT INTO finance_requests (user_id, type, process_value, request_timestamp) VALUES (?,?,?,?)";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #015";
    } else {
        $now = time();
        mysqli_stmt_bind_param($stmt, 'ssss', $_SESSION['id'], $type, $value, $now);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #016";
        } else {
            $why = "تجهيزا لسحبهم من الرصيد لصالح صاحب الحساب";
            $new_finance = new_finance_process($conn, $_SESSION['id'], 'ban_balance', $value, $why);
            if ($new_finance['error']) {
                $back['error'] = true;
                $back['message'] = $new_finance['message'];
            } else {
                $back['available'] = $new_finance['available'];
                $back['banned'] = $new_finance['banned'];
                $back['check'] = "success";
                $admins = admins($conn);
                $admins = $admins['admins'];
                foreach ($admins as $admin) {
                    send_noti($conn, $admin, 'يوجد طلب سحب رصيد جديد من أحد العملاء، إضغط هنا للإنتقال لصفحة إدارة الرصيد', 'balance');
                }
            }
        }
    }
    return $back;
}

function new_finance_process($conn, $user, $type, $value, $why)
{
    $back = array("error" => false);
    switch ($type) {
        case 'plus':
            $balance = balance($conn, $user);
            if ($balance['error']) {
                $back['error'] = true;
                $back['message'] = $balance['message'];
            } else {
                $new_balance = $balance['available'] + $value;
                $update_balance = update_balance($conn, $user, 'available_balance', $new_balance);
                if ($update_balance['error']) {
                    $back['error'] = true;
                    $back['message'] = $update_balance['message'];
                }
            }
            break;
        case "ban_balance":
            $balance = balance($conn, $user);
            if ($balance['error']) {
                $back['error'] = true;
                $back['message'] = $balance['message'];
            } else {
                $new_available = $balance['available'] - $value;
                $new_banned = $balance['banned'] + $value;
                $back['available'] = $new_available;
                $back['banned'] = $new_banned;
                $update_available = update_balance($conn, $_SESSION['id'], 'available_balance', $new_available);
                if ($update_available['error']) {
                    $back['error'] = true;
                    $back['message'] = $update_available['message'];
                } else {
                    $update_banned = update_balance($conn, $_SESSION['id'], 'banned_balance', $new_banned);
                    if ($update_banned['error']) {
                        $back['error'] = true;
                        $back['message'] = $update_banned['message'];
                    }
                }
            }
            break;
        case 'unban_balance':
            $balance = balance($conn, $user); {
                if ($balance['error']) {
                    $back['error'] = true;
                    $back['message'] = $balance['message'];
                } else {
                    $new_banned = $balance['banned'] - $value;
                    $new_available = $balance['available'] + $value;

                    $update_banned = update_balance($conn, $user, 'banned_balance', $new_banned);
                    if ($update_banned['error']) {
                        $back['error'] = true;
                        $back['message'] = $update_banned['message'];
                    } else {
                        $update_available = update_balance($conn, $user, 'available_balance', $new_available);
                        if ($update_available['error']) {
                            $back['error'] = true;
                            $back['message'] = $update_available['message'];
                        }
                    }
                }
            }
            break;

        case 'mins':
            $balance = balance($conn, $user);
            if ($balance['error']) {
                $back['error'] = true;
                $back['message'] = $balance['message'];
            } else {
                $new_banned = $balance['banned'] - $value;
                $update_banned = update_balance($conn, $user, 'banned_balance', $new_banned);
                if ($update_banned['error']) {
                    $back['error'] = true;
                    $back['message'] = $update_banned['message'];
                }
            }
            break;
    }
    if (!$back['error']) {
        $sql = "INSERT INTO finance_process (user_id, process_type, process_value, why, process_timestamp) VALUES (?,?,?,?,?)";
        $now = time();
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #017";
        } else {
            $now = time();
            mysqli_stmt_bind_param($stmt, 'sssss', $user, $type, $value, $why, $now);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #018";
            } else {
                $insert = last_process_id($conn, $user);
                if ($insert['error']) {
                    $back['error'] = true;
                    $back['message'] = $insert['message'];
                } else {
                    $back['id'] = $insert['id'];
                    send_noti($conn, $user, $why, 'account');
                }
            }
        }
    }
    return $back;
}

function request($conn, $id)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM finance_requests WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #021";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $id);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #022";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            if ($check == 0) {
                $back['error'] = true;
                $back['message'] = "هذا الطلب غير موجود";
            } else {
                $row = mysqli_fetch_assoc($result);
                $back['request'] = $row;
            }
        }
    }
    return $back;
}

function approve_add_request($conn, $id, $value)
{
    $back = array("error" => false);
    $now = time();
    $status = "Approved";
    $request = request($conn, $id);
    if ($request['error']) {
        $back['error'] = true;
        $back['message'] = $request['message'];
    } else {
        $request = $request['request'];
        $why = "بناءا على طلب إضافة رصيد (" . $request['id'] . ") بعملية تم إثبات دفعها بالوصل المرفق مع الطلب ";
        $process = new_finance_process($conn, $request['user_id'], 'plus', $value, $why);
        if ($process['error']) {
            $back['error'] = true;
            $back['message'] = $process['message'];
        } else {
            $sql = "UPDATE finance_requests SET process_value=?, process_status=?, action_timestamp=? WHERE id=?";
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #023";
            } else {
                mysqli_stmt_bind_param($stmt, 'ssss', $value, $status, $now, $id);
                if (!mysqli_stmt_execute($stmt)) {
                    $back['error'] = true;
                    $back['message'] = "خطأ تقني #024";
                } else {
                    $back['check'] = "success";
                    $title = "تمت الموافقة على طلبك لإضافة رصيد برقم#" . $request['id'] . "، وقد تم إضافة المبلغ المحول إلينا من طرفكم إلى حسابكم وقدره " . $value . " جنيهاً مصرياً";
                    $url = 'account';
                    send_noti($conn, $request['user_id'], $title, $url);
                }
            }
        }
    }
    return $back;
}

function refuse_add_request($conn, $id)
{
    $back = array("error" => false);
    $status = "Refused";
    $sql = "UPDATE finance_requests SET action_timestamp=?, process_status=? WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #025";
    } else {
        $now = time(); 
        mysqli_stmt_bind_param($stmt, 'sss', $now, $status, $id);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #026";
        } else {
            $back['check'] = "success";
            $request = request($conn, $id);
            $request = $request['request'];
            $title = "تمت رفض  طلبك لإضافة رصيد برقم#" . $request['id'] . "، إن كنت تظن ان هذا خطأ يرجى التواصل مع الدعم";
            $url = 'account';
            send_noti($conn, $request['user_id'], $title, $url);
        }
    }
    return $back;
}

function approve_withdraw_request($conn, $request_id)
{
    $back = array("error" => false);
    $request = request($conn, $request_id);
    if ($request['error']) {
        $back['error'] = true;
        $back['message'] = $request['message'];
    } else {
        $request = $request['request'];
        $why = "تنفيذ لطلب سحب الرصيد رقم (" . $request_id . ") بقيمة '" . $request['process_value'] . "' جنيه مصري";
        $new_finance = new_finance_process($conn, $request['user_id'], 'mins', $request['process_value'], $why);
        if ($new_finance['error']) {
            $back['error'] = true;
            $back['message'] = $new_finance['message'];
        } else {
            $sql = "UPDATE finance_requests SET action_timestamp=?, process_status=? WHERE id=?";
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #025";
            } else {
                $status = "Approved";
                $now = time(); 
                mysqli_stmt_bind_param($stmt, 'sss', $now, $status, $request_id);
                if (!mysqli_stmt_execute($stmt)) {
                    $back['error'] = true;
                    $back['message'] = "خطأ تقني #026";
                } else {
                    $back['check'] = "success";
                    $title = "تمت الموافقة على طلبك لسحب رصيد برقم#" . $request['id'] . "وتم خصم قيمة المبلغ من حسابك وقدره " . $request['process_value'] . " جنيهاً مصرياً";
                    $url = 'account';
                    send_noti($conn, $request['user_id'], $title, $url);
                }
            }
        }
    }
    return $back;
}

function update_balance($conn, $user, $balance_type, $value)
{
    $back = array('error' => false);
    $sql = "UPDATE accounts SET $balance_type=? WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #027";
    } else {
        mysqli_stmt_bind_param($stmt, 'ss', $value, $user);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #028";
        } else {
            $back['check'] = "success";
        }
    }
    return $back;
}

function balance($conn, $user)
{
    $back = array("error" => false);
    $sql = "SELECT available_balance, banned_balance FROM accounts WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #029";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $user);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #030";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            if ($check != 1) {
                $back['error'] = true;
                $back['message'] = "هذا المستخدم غير موجود";
            } else {
                $row = mysqli_fetch_assoc($result);
                $back['available'] = $row['available_balance'];
                $back['banned'] = $row['banned_balance'];
            }
        }
    }
    return $back;
}

// **تم تعديل دالة is_user**
function is_user($conn, $id)
{
    $back = array("error" => false);
    // جلب معلومات المستخدم من جدول accounts فقط (بدون IP/Location)
    $sql = "SELECT id, first_name, last_name, phone, nid, email, verified, noti_visit, account_status, acc_type FROM accounts WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #045";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $id);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #046";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            if ($check == 1) {
                $back['check'] = true;
                $row = mysqli_fetch_assoc($result);
                $back['user'] = $row;
            } else {
                $back['error'] = true;
                $back['message'] = "رقم الID الذي أدخلته لا ينتمي لأي حساب، يرجى التأكد من رقم الID والمحاولة مجدداً";
                $back['check'] = false;
            }
        }
    }
    return $back;
}

// **دالة جديدة لجلب بيانات الموقع من user_locations**
function get_user_location_details($conn, $user_id) {
    $back = array("error" => false);
    $sql = "SELECT * FROM user_locations WHERE user_id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #Location001";
    } else {
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #Location002";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                $back['location'] = mysqli_fetch_assoc($result);
                $back['check'] = true;
            } else {
                $back['check'] = false;
                $back['message'] = "لا توجد بيانات موقع لهذا المستخدم.";
            }
        }
    }
    return $back;
}


function new_message($conn, $mess, $to_type, $to_id)
{
    $back = array("error" => false);
    $admins = admins($conn);
    if ($admins['error']) {
        $back = $admins;
    } else {
        if ($admins['num'] == 0) {
            $back['error'] = true;
            $back['message'] = "لا يمكن للموقع ان يعمل بدون مسئول واحد على الأقل";
        } else {
            $admins = $admins['admins'];
        }
    }
    $receivers = array();
    if ($to_type == "report") {
        $report = report($conn, $to_id);
        $report = $report['report'];
        if ($report['report_status'] == "closed") {
            $open_report = action_report($conn, $to_id, 'running');
            if ($open_report['error']) {
                $back = $open_report;
            }
        }
        if ($report['user'] == $_SESSION['id']) {
            foreach($admins as $admin){
                $receivers[] = $admin;
            }
        } else {
            $receivers[] = $report['user'];
        }
    } else {
        $order = order($conn, $to_id);
        $order = $order['order'];
        if ($order['buyer'] != $_SESSION['id'] && $order['seller'] != $_SESSION['id']) {
            $receivers[] = $order['buyer'];
            $receivers[] = $order['seller'];
        } elseif ($order['buyer'] != $_SESSION['id']) {
            $receivers[] = $order['buyer'];
        } else {
            $receivers[] = $order['seller'];
        }
    }
    if (!$mess) {
        $back['error'] = true;
        $back['message'] = "يجب كتابة رسالة";
    } else {
        $mess_from = $_SESSION['id'];
        $mess_from_name = short_name_white($_SESSION);
        $now = time();
        $sql = "INSERT INTO messages (mess, mess_from, mess_from_name, to_type, to_id, mess_timestamp) VALUES (?,?,?,?,?,?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #031";
        } else {
            mysqli_stmt_bind_param($stmt, 'ssssss', $mess, $mess_from, $mess_from_name, $to_type, $to_id, $now);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #032";
            } else {
                $back['date'] = arabicDate($now);
                $back['time'] = timeHour($now);
                $back['sender'] = $mess_from;
                $back['sender_name'] = $mess_from_name;
                $back['mess'] = nl2br($mess);
                // Sending Notifications
                foreach ($receivers as $receiver) {
                    $title = "لديك رسالة جديدة في " . visible_message_type($to_type) . ' رقم #' . $to_id;
                    $url = $to_type . '?id=' . $to_id;
                    $noti = send_noti($conn, $receiver, $title, $url);
                    if ($noti['error']) {
                        $back['error'] = true;
                        $back['message'] = $noti['message'];
                    }
                }
                return $back;
            }
        }
    }
}
function new_report($conn, $user, $title, $description)
{
    $back = array("error" => false);
    $now = time();
    $status = "running";
    if (!$user || !$title | !$description) {
        $back['error'] = true;
        $back['message'] = "خطأ، يجب إدخال كافة البيانات";
    } else {
        $sql = "INSERT INTO reports (title, description, user, report_timestamp, report_status) VALUES (?,?,?,?,?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #033";
        } else {
            mysqli_stmt_bind_param($stmt, 'sssss', $title, $description, $user, $now, $status);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #034";
            } else {
                $report = last_id($conn, "reports");
                if ($report['error']) {
                    $back['error'] = true;
                    $back['message'] = $report['message'];
                } else {
                    $back['id'] = $report['id'];
                    $back['message'] = "تم إرسال شكوتك بنجاح وسيتم التعامل معها في أسرع وقت ممكن";
                    $admins = admins($conn);
                    $admins = $admins['admins'];
                    foreach ($admins as $admin) {
                        send_noti($conn, $admin, 'يوجد شكوى جديدة من أحد العملاء إضغط للإطلاع عليها', 'report?id=' . $report['id']);
                    }
                    send_noti($conn, $user, 'تم إرسال شكوتك بنجاح برقم (#' . $report['id'] . ') وسيتم التعامل معها في أسرع وقت ممكن', 'report?id=' . $report['id']);
                }
            }
        }
    }
    return $back;
}
function action_report($conn, $report, $action)
{
    $back = array("error" => false);
    $sql = "UPDATE reports SET report_status=? WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #035";
    } else {
        mysqli_stmt_bind_param($stmt, 'ss', $action, $report);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #036";
        } else {
            $report = report($conn, $report);
            $report = $report['report'];
            $title = "تم " . visible_report_action($action) . " الشكوى الخاصة بك برقم #" . $report['id'];
            $url = "report?id=" . $report['id'];
            send_noti($conn, $report['user'], $title, $url);
        }
    }
    return $back;
}
function price_detailes($conn, $price)
{
    $tax = tax($conn);
    $tax = $tax['tax'];
    $order_tax = ($tax / 100) * $price;
    $order_seller = $price - $order_tax;
    $back['tax'] = $order_tax;
    $back['seller'] = $order_seller;
    return $back;
}
function new_order($conn, $title, $cat, $description, $buyer, $seller, $price, $deadline, $warranty)
{
    $back = array("error" => false);
    $price_details = price_detailes($conn, $price);
    $tax_val = $price_details['tax'];
    $seller_val = $price_details['seller'];
    $start = time();
    $end = $start + ($deadline * 86400);
    $warranty_end = $end + ($warranty * 86400);
    $status = "running";
    $balance = balance($conn, $_SESSION['id']);
    if ($balance['available'] < $price) {
        $back['error'] = true;
        $back['message'] = "لا يوجد لديك رصيد متاح يكفي لتسديد قيمة هذا الطلب";
    } else {
        $seller_check = is_user($conn, $seller);
        if (!$seller_check['check']) {
            $back['error'] = true;
            $back['message'] = "حساب البائع هذا غير موجود بالموقع";
        } else {
            if ($seller_check['user']['account_status'] != "Active") {
                $back['error'] = true;
                $back['message'] = "لا يمكن إرسال طلبات لهذا المستخدم في الوقت الحالي";
            } else {
                $cat_query = cat($conn, $cat);
                if($cat_query['error']){
                    $back = $cat_query;
                }else{
                    if($cat_query['num'] < 1 && $cat != 0){
                        $back['error'];
                        $back['message'] = "يجب إختيار تصنيف صالح أو عدم إختيار تصنيف من الأساس";
                    }else{
                        if($cat > 0){
                            $cat_name = $cat_query['cat']['name'];
                        }else{
                            $cat_name = "لا تصنيف";
                        }
                        $sql = "INSERT INTO orders (title, cat, cat_name, description, buyer, seller, price, tax_val, seller_val, deadline, warranty, order_start, order_end, warranty_end, order_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                        $stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt, $sql)) {
                            $back['error'] = true;
                            $back['message'] = "خطأ تقني #037";
                        } else {
                            mysqli_stmt_bind_param($stmt, 'sssssssssssssss', $title, $cat, $cat_name, $description, $buyer, $seller, $price, $tax_val, $seller_val, $deadline, $warranty, $start, $end, $warranty_end, $status);
                            if (!mysqli_stmt_execute($stmt)) {
                                $back['error'] = true;
                                $back['message'] = "خطأ تقني #038";
                            } else {
                                $order = last_order_id($conn, $_SESSION['id']);
                                if ($order['error']) {
                                    $back['error'] = true;
                                    $back['message'] = $order['message'];
                                } else {
                                    $back['id'] = $order['id'];
                                    // Send Notification
                                    $noti_title = "لديك طلب جديد برقم #" . $back['id'] . " من قبل المشتري: " . short_name($_SESSION);
                                    $ban = new_finance_process($conn, $buyer, 'ban_balance', $price, "تمهيدا لتسديد قيمة الطلب (#" . $back['id'] . ")");
                                    if ($ban['error']) {
                                        delete_order($conn, $back['id']);
                                        $back = $ban;
                                    } else {
                                        send_noti($conn, $seller, $noti_title, 'order?id=' . $back['id']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $back;
}
function last_order_id($conn, $user)
{
    $back = array("error" => false);
    $sql = "SELECT id FROM orders WHERE buyer=? ORDER BY id DESC LIMIT 1";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #039";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $user);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #040";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $back['id'] = $row['id'];
        }
    }
    return $back;
}
function action_order($conn, $order, $action)
{
    $back = array("error" => false);
    $sql = "UPDATE orders SET order_status=? WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #041";
    } else {
        mysqli_stmt_bind_param($stmt, 'ss', $action, $order);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #042";
        } else {
            $order = order($conn, $order);
            $order = $order['order'];
            switch ($action) {
                case 'waiting acceptance':
                    $noti_title = "تم تسليم الطلب الخاص بك برقم #" . $order['id'];
                    break;
                case 'submitted':
                    $noti_title = "تم الموافقة على إستلام الطلب الخاص بك برقم #" . $order['id'];
                    break;
                case 'cancelled':
                    $noti_title = "تم إلغاء الطلب الخاص بك برقم #" . $order['id'];
                    break;
            }
            $url = "order?id=" . $order['id'];
            $buyer_noti = send_noti($conn, $order['buyer'], $noti_title, $url);
            if ($buyer_noti['error']) {
                $back = $buyer_noti;
            } else {
                $seller_noti = send_noti($conn, $order['seller'], $noti_title, $url);
                if ($seller_noti['error']) {
                    $back = $seller_noti;
                }
            }
        }
    }
    return $back;
}
function order($conn, $order)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM orders WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #043";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $order);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #044";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            if ($check != 1) {
                $back['error'] = true;
                $back['message'] = "هذا الطلب غير موجود ورقمه: ";
            } else {
                $row = mysqli_fetch_assoc($result);
                $back['order'] = $row;
            }
        }
    }
    return $back;
}
function submit_order($conn, $order)
{
    $action = action_order($conn, $order, "waiting acceptance");
    return $action;
}
function accept_order($conn, $order_id)
{
    $back = array("error" => false);
    $order = order($conn, $order_id);
    if ($order['error']) {
        $back['error'] = true;
        $back['message'] = $order['message'];
    } else {
        $order = $order['order'];
        // Discount buyer balance
        $why = 'تم خصم المبلغ من "الرصيد المرهون لدى الموقع" لتسديد قيمة الطلب (' . $order_id . ')';
        $buyer = new_finance_process($conn, $order['buyer'], "mins", $order['price'], $why);
        if ($buyer['error']) {
            $back['error'] = true;
            $back['message'] = $buyer['message'];
        } else {
            // Adding seller balance
            $why = "مقابل تنفيذ الطلب (" . $order_id . ")";
            $seller = new_finance_process($conn, $order['seller'], "plus", $order['seller_val'], $why);
            if ($seller['error']) {
                $back['error'] = true;
                $back['message'] = $seller['message'];
            } else {
                $why = "أرباح عن الطلب (" . $order_id . ")";
                $profits = new_finance_process($conn, $_SESSION['admin'], "plus", $order['tax_val'], $why);
                if ($profits['error']) {
                    $back['error'] = true;
                    $back['message'] = $profits['message'];
                } else {
                    $action = action_order($conn, $order_id, "submitted");
                    if ($action['error']) {
                        $back['error'] = true;
                        $baxk['message'] = $action['message'];
                    } else {
                        if ($order['warranty'] > 0) {
                            $why = "تنفيذ لبند الضمان لعدد الأيام المحددة (" . $order['warranty'] . ") في الطلب رقم #" . $order_id;
                            $ban_balance = new_finance_process($conn, $order['seller'], 'ban_balance', $order['seller_val'], $why);
                            if ($ban_balance['error']) {
                                $back['error'] = true;
                                $back['message'] = $ban_balance['message'];
                            } else {
                                $warranty = warranty_order($conn, $order_id, $order['seller'], $order['seller_val'], $order['warranty']);
                                if ($warranty['error']) {
                                    $back['error'] = true;
                                    $back['message'] = $warranty['message'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $back;
}
function cancel_order($conn, $order_id)
{
    $back = array("error" => false);
    $now = time();
    $able = false;
    $order = order($conn, $order_id);
    if ($order['error']) {
        $back['error'] = true;
        $back['message'] = $order['message'];
    } else {
        $order = $order['order'];
        if ($_SESSION['id'] == $order['seller'] || $_SESSION['acc_type'] == "admin") {
            $able = true;
        } else {
            if ($now > $order['order_end']) {
                $able = true;
            }
        }
        if (!$able) {
            $back['error'] = true;
            $back['message'] = "عذراً لا يمكنك إلغاء هذا الطلب في الوقت الحالي";
        } else {
            $why = "إسترجاع قيمة الطلب (" . $order_id . ")";
            $discount_value = 0.01 * $order['price'];
            $unbanned_value = $order['price'] - $discount_value;
            $new_finance = new_finance_process($conn, $order['buyer'], 'unban_balance', $unbanned_value, $why);
            if ($new_finance['error']) {
                $back['error'] = true;
                $back['message'] = $new_finance['message'];
            } else {
                $why = "قيمة ال1% خصم عن الطلب ملغي";
                $discount_value = 0.01 * $order['price'];
                $discount = new_finance_process($conn, $order['buyer'], 'mins', $discount_value, $why);
                if ($discount['error']) {
                    $back['error'] = true;
                    $back['message'] = $discount['message'];
                } else {
                    $why = "قيمة إلغاء الطلب #" . $order_id;
                    $admin_profits =  new_finance_process($conn, $_SESSION['admin'], 'plus', $discount_value, $why);
                    if ($admin_profits['error']) {
                        $back['error'] = true;
                        $back['message'] = $admin_profits['message'];
                    } else {
                        $action = action_order($conn, $order_id, "cancelled");
                        if ($action['error']) {
                            $back['error'] = true;
                            $baxk['message'] = $action['message'];
                        }
                    }
                }
            }
        }
    }
    return $back;
}
function refuse_order($conn, $order, $reason)
{
    $back = array("error" => false);
    $order = order($conn, $order);
    if ($order['error']) {
        $back['error'] = true;
        $back['message'] = $order['message'];
    } else {
        $order = $order['order'];
        $title = "أرفض إستلام الطلب رقم " . $order['id'];
        $report = new_report($conn, $order['buyer'], $title, $reason);
        if ($report['error']) {
            $back['error'] = true;
            $back['message'] = $report['message'];
        } else {
            $back['report'] = $report['id'];
        }
    }
    return $back;
}
function new_attachment($conn, $for_id, $file)
{
    $back = array("error" => false);
    $now = time();
    $name = $file['name'];
    $type = $file['type'];
    $tmp = $file['tmp_name'];
    $file_error = $file['error'];
    $size = $file['size'];
    $ext = explode('.', $name);
    $ext = strtolower(end($ext));
    if ($file_error == 4) {
        $back['error'] = true;
        $back['message'] = "يجب إختيار ملف أولاً";
    } else {
        $allowed_ext = array('png', 'jpg', 'jpeg', 'xlsx', 'pdf', 'docx', 'txt', 'rtf', 'rar', 'zip');
        if (!in_array($ext, $allowed_ext)) {
            $back['error'] = true;
            $back['message'] = "صيغة الملف غير مسموح بها";
        } else {
            $new_name = "Wastetco-" . $for_id . random_int(10000, 99999) . "." . $ext;
            $path = '../attachments/' . $new_name;
            if ($size > 10000000) {
                $back['error'] = true;
                $back['message'] = "حجم الملف اكبر من المسموح به";
            } else {
                while (file_exists($path)) {
                    $new_name = "Wastetco - " . $for_id . random_int(10000, 99999) . "." . $ext;
                    $path = '../attachments/' . $new_name;
                }
                if (!move_uploaded_file($tmp, $path)) {
                    $back['error'] = true;
                    $back['message'] = "لم نستطع تحميل صورة البطاقة الخاصة بك، يرجى المحاولة مجدداً";
                } else {
                    $sql = "INSERT INTO attachments(order_id, old_name, new_name, uploader, upload_timestamp) VALUES (?,?,?,?,?)";
                    $stmt = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($stmt, $sql)) {
                        $back['error'] = true;
                        $back['message'] = "خطأ تقني #047";
                    } else {
                        mysqli_stmt_bind_param($stmt, 'sssss', $for_id, $name, $new_name, $_SESSION['id'], $now);
                        if (!mysqli_stmt_execute($stmt)) {
                            $back['error'] = true;
                            $back['message'] = "خطأ تقني #048";
                        }
                    }
                    $back['file'] = $new_name;
                }
            }
        }
    }
    return $back;
}
function view_order_status($status)
{
    switch ($status) {
        case 'running':
            $data = "جاري التنفيذ";
            break;
        case 'waiting acceptance':
            $data = "في إنتظار الموافقة";
            break;
        case 'submitted':
            $data = "تم التسليم";
            break;
        case 'cancelled':
            $data = "ملغي";
            break;
        default:
            $data = "خطأ";
            break;
    }
    return $data;
}
function order_permissions($order)
{
    $back = array("error" => false);
    $now = time();
    $user = $_SESSION['id'];
    if ($_SESSION['acc_type'] == "admin") {
        $type = "admin";
    } else {
        if ($user == $order['buyer']) {
            $type = "buyer";
        } elseif ($user == $order['seller']) {
            $type = "seller";
        } else {
            $type = "denied";
        }
    }

    $permissions = array();
    switch ($type) {
        case 'buyer':
            if ($order['order_status'] == "running"  && $now > $order['order_end']) {
                $permissions[] = "Cancel";
            }
            if ($order['order_status'] == "waiting acceptance") {
                $permissions[] = "Accept";
                $permissions[] = "Refuse";
            }
            break;
        case 'seller':
            if ($order['order_status'] == "running") {
                $permissions[] = "Cancel";
                $permissions[] = "Submit";
            }
            break;
        case 'admin':
            if ($order['order_status'] == "running") {
                $permissions[] = "Cancel";
            }
            if ($order['order_status'] == "waiting acceptance") {
                $permissions[] = "Accept";
                $permissions[] = "Refuse";
                $permissions[] = "Cancel";
            }
            break;
        default:
            $back['error'] = true;
            $back['message'] = "خطأ، ليس لك حق الوصول لهذه الصفحة";
            break;
    }

    $back['type'] = $type;
    $back['num'] = count($permissions);
    $back['permissions'] = $permissions;

    return $back;
}
function prepare_string($string)
{
    $string = str_replace('\n', '&#92;&#110;', $string);
    $url = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
    $string = preg_replace($url, '<a href="redirect?link=http$2://$4" target="_blank" title="$0">$0</a>', $string);
    return $string;
}
function order_attachments($conn, $order)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM attachments WHERE order_id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #049";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $order);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #050";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            $back['num'] = $check;
            if ($check < 1) {
                $back['message'] = "لا يوجد مرفقات في هذا الطلب حتى الآن";
            } else {
                $attachments = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $attachments[] = $row;
                }
                $back['attachments'] = $attachments;
            }
        }
    }
    return $back;
}
function attachment_item($old_name, $new_name)
{
    $back = array("error" => false);
    $item = "<a target='_blank' href='../attachments/" . $new_name . "' class='btn btn-sm btn-outline-secondary mb-3 ml-3'>
                <i class='fas fa-file ml-2'></i>
                " . $old_name . "
            </a>
        ";
    return $item;
}
function messages($conn, $id, $type)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM messages WHERE to_id=? AND to_type=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #051";
    } else {
        mysqli_stmt_bind_param($stmt, 'ss', $id, $type);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #052";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            $back['num'] = $check;
            if ($check < 1) {
                $back['message'] = "لا يوجد رسائل في هذا الطلب حتى الآن";
            } else {
                $messages = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $messages[] = $row;
                }
                $back['messages'] = $messages;
            }
        }
    }
    return $back;
}
function message_item($conn, $message)
{
    $date = arabicDate($message['mess_timestamp']);
    $time = timeHour($message['mess_timestamp']);
    $mess_from = $message['mess_from'];
    $mess_from_name = $message['mess_from_name'];
    $mess = nl2br($message['mess']);
    if ($_SESSION['id'] == $mess_from) {
        $color = "mess-sender";
    } else {
        $color = "mess-receiver";
    }
    $is_admin = is_admin($conn, $mess_from);
    if ($is_admin['error']) {
        sendError($is_admin['message']);
    } else {
        if ($is_admin['check']) {
            $color = "bg-danger";
        }
    }
    $message_item = '<table class="table table-responsive table-borderless message-table text-light p-2 mb-3 ' . $color . '">
                    <tr>
                        <th class="text-center">
                            ' . $mess_from_name . ' <br>
                            [' . $mess_from . ']
                        </th>
                        <td class="message-text">
                        ' . $mess . '
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center" colspan="2">
                            <i class="fas fa-calendar-alt ml-1"></i> : ' . $date . '
                            <i class="fas fa-clock ml-1"></i> : ' . $time . '
                        </td>
                    </tr>
                </table>';
    // $message_item = "<div class='messages-item ".$color."'>
    //         <div class='container'>
    //             <div class='user-area text-center'>
    //                 " . $mess_from_name . " <br>
    //                 [" . $mess_from . " ]
    //             </div>
    //             <div class='message-area'>
    //                 <p class='message'>
    //                 " . $mess . " 
    //                     <div class='mt-2'>
    //                     <i class='fas fa-calendar-alt ml-1'></i>
    //                     التاريخ:
    //                     " . $date . "
    //                     <i class='fas fa-clock ml-1'></i>
    //                     الوقت:
    //                     " . $time . " 
    //                     </div>
    //                 </p>

    //             </div>
    //         </div>
    //     </div>";
    return $message_item;
}
function orders($conn, $user, $type, $limit)
{
    $back = array("error" => false);
    switch ($type) {
        case 'all':
            if (empty($limit)) {
                $sql = "SELECT * FROM orders WHERE seller=? OR buyer=? ORDER by id DESC";
            } else {
                $sql = "SELECT * FROM orders WHERE seller=? OR buyer=? ORDER by id DESC LIMIT $limit";
            }
            break;
        case 'sales':
            if (empty($limit)) {
                $sql = "SELECT * FROM orders WHERE seller=? ORDER by id DESC";
            } else {
                $sql = "SELECT * FROM orders WHERE seller=? ORDER by id DESC LIMIT $limit";
            }
            break;
        case 'purches':
            if (empty($limit)) {
                $sql = "SELECT * FROM orders WHERE buyer=? ORDER by id DESC";
            } else {
                $sql = "SELECT * FROM orders WHERE buyer=? ORDER by id DESC LIMIT $limit";
            }
            break;
    }
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #053";
    } else {
        if ($type == "all") {
            mysqli_stmt_bind_param($stmt, 'ss', $user, $user);
        } else {
            mysqli_stmt_bind_param($stmt, 's', $user);
        }
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #054";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            $back['num'] = $check;
            if ($check < 1) {
                $back['message'] = "لا يوجد طلبات حتى الآن";
            } else {
                $orders = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $orders[] = $row;
                }
                $back['orders'] = $orders;
            }
        }
    }
    return $back;
}
function sidebar_order_item($conn, $order)
{
    $buyer = $order['buyer'];
    $seller = $order['seller'];
    if ($buyer == $_SESSION['id']) {
        $client = is_user($conn, $seller);
        $client = $client['user'];
        $client_name = short_name($client);
        $arrow  = "<i class='fas fa-long-arrow-alt-left mr-2 ml-2 text-danger'></i>";
    } else {
        $client = is_user($conn, $buyer);
        $client = $client['user'];
        $client_name = short_name($client);
        $arrow  = "<i class='fas fa-long-arrow-alt-right mr-2 ml-2 text-success'></i>";
    }
    $item = "<li class='last-orders-item'>
        <div class='container-fluid'>
            <div class='first-line'>

                <a href='order?id=" . $order['id'] . "' class='text-dark font-weight-bold'>" . $order['title'] . "</a>
                
                " . $arrow . "
                <a href='#' class='text-dark'>" . $client_name . "</a>
            </div>

            <div class='second-line'>

                <p class='text-secondary'>
                    (" . arabicDate($order['order_start']) . " إلى " . arabicDate($order['order_end']) . ") " . $order['price'] . " جنيه مصري
                </p>
            </div>
        </div>
    </li>";
    return $item;
}
function accounts($conn, $status)
{
    $back = array("error" => false);
    $sql = "SELECT id, first_name, last_name, email, phone, nid, account_status, available_balance, banned_balance, acc_type, verified FROM accounts WHERE account_status=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #055";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $status);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #056";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            $back['num'] = $check;
            if ($check < 1) {
                $back['message'] = "لا يوجد حسابات لعملاء في هذا التصنيف حالياً";
            } else {
                $accounts = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $accounts[] = $row;
                }
                $back['accounts'] = $accounts;
            }
        }
    }
    return $back;
}
function active_account_row($n, $account)
{
?>
    <tr>
        <td><?= $n ?></td>
        <td><a href="manage_account?id=<?= $account['id'] ?>" class="text-dark font-weight-bold"><?= short_name($account) ?></a></td>
        <td><?= $account['id'] ?></td>
        <td><?= $account['email'] ?></td>
        <td><?= $account['phone'] ?></td>
        <?php
        if ($account['verified'] == "yes") {
        ?>
            <td><button type="button" class="btn btn-sm btn-danger action-account" action="no" account="<?= $account['id'] ?>"><i class="fas fa-check-circle ml-1"></i>نزع التوثيق</button></td>
        <?php
        } else {
        ?>
            <td><button type="button" class="btn btn-sm btn-success action-account" action="yes" account="<?= $account['id'] ?>"><i class="fas fa-check-circle ml-1"></i>توثيق</button></td>
        <?php
        }
        ?>
        <td><button type="button" class="btn btn-sm btn-danger ban-account" account="<?= $account['id'] ?>"><i class="fas fa-ban ml-1"></i>حظر</button></td>
    </tr>
<?php
}
function banned_account_row($n, $account)
{
?>
    <tr>
        <td><?= $n ?></td>
        <td><a href="manage_account?id=<?= $account['id'] ?>" class="text-dark font-weight-bold"><?= short_name($account) ?></a></td>
        <td><?= $account['id'] ?></td>
        <td><?= $account['email'] ?></td>
        <td><?= $account['phone'] ?></td>
        <td><button type="button" class="btn btn-sm btn-success reactive-account" account="<?= $account['id'] ?>"><i class="fas fa-check ml-1"></i>رفع الحظر</button></td>
    </tr>
<?php
}
function new_account_row($n, $account)
{
?>
    <tr>
        <td><?= $n ?></td>
        <td><a href="manage_account?id=<?= $account['id'] ?>" class="text-dark font-weight-bold"><?= short_name($account) ?></a></td>
        <td><?= $account['id'] ?></td>
        <td><?= $account['email'] ?></td>
        <td><?= $account['phone'] ?></td>
        <td><a href="../attachments/id_cards/<?= $account['id'] ?>.jpg" target="_blank">صورة البطاقة</a></td>
        <td><button type="button" class="btn btn-sm btn-success active-account" account="<?= $account['id'] ?>"><i class="fas fa-check ml-1"></i>قبول</button></td>
        <td><button type="button" class="btn btn-sm btn-danger ban-account" account="<?= $account['id'] ?>"><i class="fas fa-ban ml-1"></i>رفض</button></td>
    </tr>
    <?php
}
function active_ban_account($conn, $id, $status)
{
    $back = array("error" => false);
    if (!$id || !$status) {
        $back['error'] = true;
        $back['message'] = "خطأ، هناك بيانات غير مكتملة";
    } else {
        if ($id == $_SESSION['id']) {
            $back['error'] = true;
            $back['message'] = "لا يمكنك حظر نفسك";
        } else {
            $sql = "UPDATE accounts SET account_status=? WHERE id=?";
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #057";
            } else {
                mysqli_stmt_bind_param($stmt, 'ss', $status, $id);
                if (!mysqli_stmt_execute($stmt)) {
                    $back['error'] = true;
                    $back['message'] = "خطأ تقني #058";
                }
            }
        }
    }
    return $back;
}
function active_account($conn, $id, $nid)
{
    $back = array("error" => false);
    if (!$id || !$nid) {
        $back['error'] = true;
        $back['message'] = "هناك خطأ، البيانات غير مكتملة";
    } else {
        $status = "Active";
        $sql = "UPDATE accounts SET nid=?, account_status=? WHERE id=?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #059";
        } else {
            mysqli_stmt_bind_param($stmt, 'sss', $nid, $status, $id);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #060";
            } else {
                $user = is_user($conn, $id);
                if ($user['error']) {
                    $back = $user;
                } else {
                    $user = $user['user'];
                    $from = "support@wastetco.com";
                    $headers = "From:" . $from;
                    mail($user['email'], "نتيجة متابعة حسابك على واسطتكو", "تمت الموافقة على حسابك بنجاح يمكنك الآن تسجيل الدخول على الموقع", $headers);
                }
            }
        }
    }
    return $back;
}
function admin_balance($conn)
{
    $back = array("error" => false);
    $plus =  process_type($conn, 'plus');
    if ($plus['error']) {
        $back['error'] = true;
        $back['message'] = $plus['message'];
    } else {
        $mins = process_type($conn, 'mins');
        if ($mins['error']) {
            $back['error'] = true;
            $back['message'] = $mins['message'];
        } else {
            $back['plus'] = $plus['sum'];
            $back['mins'] = $mins['sum'];
            $back['now'] = $plus['sum'] - $mins['sum'];
        }
    }
    return $back;
}
function process_type($conn, $type)
{
    $back = array("error" => false);
    $sql = "SELECT process_value FROM finance_process WHERE process_type=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #061";
    } else {
        $process_type = "plus";
        mysqli_stmt_bind_param($stmt, 's', $type);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #062";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            $back['check'] = $check;
            if ($check > 0) {
                $sum = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $sum = $sum + $row['process_value'];
                }
                $back['sum'] = $sum;
            } else {
                $back['sum'] = 0;
            }
        }
    }
    return $back;
}
function requests($conn, $type, $status)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM finance_requests WHERE type=? AND process_status=? ORDER BY id DESC";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #063";
    } else {
        mysqli_stmt_bind_param($stmt, 'ss', $type, $status);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #064";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            $back['check'] = $check;
            if ($check > 0) {
                $sum = 0;
                $requests = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $requests[] = $row;
                }
                $back['requests'] = $requests;
            }
        }
    }
    return $back;
}
function add_request_row($conn, $n, $request)
{
    $sender = is_user($conn, $request['user_id']);
    if ($sender['error']) {
        $back['error'] =  true;
        $back['message'] = $sender['message'];
    } else {
        $sender = $sender['user'];
    ?>
        <tr>
            <td><?= $n ?></td>
            <td><a href="manage_account?id=<?= $sender['id'] ?>" class="text-dark font-weight-bold"><?= short_name($sender) ?></a></td>
            <td><?= $sender['id'] ?></td>
            <td><?= $sender['phone'] ?></td>
            <td><a href="../attachments/receipts/<?= $request['process_code'] ?>" target="_blank">إيصال الدفع</a></td>
            <td><button type="button" class="btn btn-sm btn-success accept-add-request" request="<?= $request['id'] ?>"><i class="fas fa-check ml-1"></i>قبول</button></td>
            <td><button type="button" class="btn btn-sm btn-danger refuse-add-request" request="<?= $request['id'] ?>"><i class="fas fa-ban ml-1"></i>رفض</button></td>
        </tr>
    <?php
    }
}
function withdraw_request_row($conn, $n, $request)
{
    $sender = is_user($conn, $request['user_id']);
    if ($sender['error']) {
        $back['error'] =  true;
        $back['message'] = $sender['message'];
    } else {
        $sender = $sender['user'];
    ?>
        <tr>
            <td><?= $n ?></td>
            <td><a href="manage_account?id=<?= $sender['id'] ?>" class="text-dark font-weight-bold"><?= short_name($sender) ?></a></td>
            <td><?= $sender['id'] ?></td>
            <td><?= $sender['phone'] ?></td>
            <td><?= $request['process_value'] ?></td>
            <td><button type="button" class="btn btn-sm btn-success accept-withdraw-request" request="<?= $request['id'] ?>"><i class="fas fa-check ml-1"></i>قبول</button></td>
        </tr>
    <?php
    }
}
function view_process_type($type)
{
    switch ($type) {
        case 'plus':
            $data = "إضافة";
            break;
        case 'mins':
            $data = "خصم";
            break;
        case 'ban_balance':
            $data = "رهن";
            break;
        case 'unban_balance':
            $data = "فك رهن";
            break;
    }
    return $data;
}
function finance_process($conn, $user, $limit)
{
    $back = array("error" => false);
    if ($user == "all") {
        if ($limit == "all") {
            $sql = "SELECT * FROM finance_process ORDER BY id DESC";
        } else {
            $sql = "SELECT * FROM finance_process ORDER BY id DESC LIMIT $limit";
        }
    } else {
        if ($limit == "all") {
            $sql = "SELECT * FROM finance_process WHERE user_id=? ORDER BY id DESC";
        } else {
            $sql = "SELECT * FROM finance_process WHERE user_id=? ORDER BY id DESC LIMIT $limit";
        }
    }
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #065";
    } else {
        if ($user != "all") {
            mysqli_stmt_bind_param($stmt, 's', $user);
        }
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #066";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            $back['check'] = $check;
            if ($check > 0) {
                $process = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $process[] = $row;
                }
                $back['process'] = $process;
            }
        }
    }
    return $back;
}
function finance_process_row($conn, $n, $process)
{
    $sender = is_user($conn, $process['user_id']);
    if ($sender['error']) {
        $back['error'] =  true;
        $back['message'] = $sender['message'];
    } else {
        $sender = $sender['user'];
    ?>
        <tr>
            <td><?= $n ?></td>
            <td class="font-weight-bold process-id" code="<?= $process['id'] ?>" name="<?= short_name($sender) ?>" phone="<?= $sender['phone'] ?>" type="<?= view_process_type($process['process_type']) ?>" value="<?= $process['process_value'] ?>" reason="<?= $process['why'] ?>" date="<?= arabicDate($process['process_timestamp']) ?>" hour="<?= timeHour($process['process_timestamp']) ?>"><?= $process['id'] ?></td>
            <td><a href="manage_account?id=<?= $sender['id'] ?>" class="text-dark font-weight-bold"><?= short_name($sender) ?></a></td>
            <td><?= $sender['id'] ?></td>
            <td><?= $sender['phone'] ?></td>
            <td><?= view_process_type($process['process_type']) ?></td>
            <td><?= $process['process_value'] ?></td>
            <td><?= arabicDate($process['process_timestamp']) ?></td>
            <td><?= timeHour($process['process_timestamp']) ?></td>
        </tr>
    <?php
    }
}
function sidebar_process_item($process)
{
    switch ($process['process_type']) {
        case 'plus':
            $icon = "<i class='fas fa-plus text-success font-weight-bold ml-2'></i>";
            break;

        case 'mins':
            $icon = "<i class='fas fa-minus text-danger font-weight-bold ml-2'></i>";
            break;
        case 'ban_balance':
            $icon = "<i class='fas fa-ban text-danger font-weight-bold ml-2'></i>";
            break;

        case 'unban_balance':
            $icon = "<i class='fas fa-ban text-success font-weight-bold ml-2'></i>";
            break;
    }
    $item = "<li class='last-finance-process-item'>
            <div class='container-fluid'>
                <div class='first-line'>
                    " . $icon . "
                    <span class='finance-process-value'>" . $process['process_value'] . "</span> جنيه مصري
                </div>
                <div class='second-line'>
                    <span class='finance-process-date'>" . arabicDate($process['process_timestamp']) . "</span>
                </div>
            </div>
        </li>";
    return $item;
}
function purches_item($conn, $purches)
{
    $seller = is_user($conn, $purches['seller']);
    $seller = $seller['user'];
    $seller_name = short_name($seller);
    $item = "<div class='purches-item p-2'>
            <a href='order?id=" . $purches['id'] . "' class='lead text-dark font-weight-bold'>" . $purches['title'] . "</a>
            <table class='table table-responsive table-borderless'>
                <tr>
                    <td>البائع: <br>" . $seller_name . "</td>
                    <td>الحالة: <br>" . view_order_status($purches['order_status']) . "</td>
                    <td>التكلفة: <br> " . $purches['price'] . " ج.م</td>
                    <td>ميعاد التسليم: <br>" . arabicDate($purches['order_end']) . "</td>
                    <td>نهاية الضمان: <br>" . arabicDate($purches['warranty_end']) . "</td>
                </tr>
            </table>
        </div>";
    return $item;
}
function sales_item($conn, $sales)
{
    $buyer_data = is_user($conn, $sales['buyer']); 
    $buyer = $buyer_data['user'];
    $buyer_name = short_name($buyer);
    $item = "<div class='sales-item p-2'>
            <a href='order?id=" . $sales['id'] . "' class='lead text-dark font-weight-bold'>" . $sales['title'] . "</a>
            <table class='table table-responsive table-borderless'>
                <tr>
                    <td>المشتري: <br>" . $buyer_name . "</td>
                    <td>الحالة: <br>" . view_order_status($sales['order_status']) . "</td>
                    <td>التكلفة: <br> " . $sales['price'] . " ج.م</td>
                    <td>ميعاد التسليم: <br>" . arabicDate($sales['order_end']) . "</td>
                    <td>نهاية الضمان: <br>" . arabicDate($sales['warranty_end']) . "</td>
                </tr>
            </table>
        </div>";
    return $item;
}
function process_row($process)
{
    switch ($process['process_type']) {
        case 'plus':
            $icon = "<i class='fas fa-plus text-success font-weight-bold ml-1'></i>";
            break;

        case 'mins':
            $icon = "<i class='fas fa-minus text-danger font-weight-bold ml-1'></i>";
            break;
        case 'ban_balance':
            $icon = "<i class='fas fa-ban text-danger font-weight-bold ml-1'></i>";
            break;

        case 'unban_balance':
            $icon = "<i class='fas fa-ban text-success font-weight-bold ml-1'></i>";
            break;
    }
    $item = "<tr>
                <td>
                   " . $icon . view_process_type($process['process_type']) . " 
                </td>
                <td>" . $process['process_value'] . " ج.م</td>
                <td>
                " . $process['why'] . "
                </td>
                <td>" . arabicDate($process['process_timestamp']) . "</td>
                <td>" . timeHour($process['process_timestamp']) . "</td>
            </tr>";
    return $item;
}
function admin_update_phone($conn, $id, $phone)
{
    $back = array("error" => false);
    if (!$id || !$phone) {
        $back['error'] = true;
        $back['message'] = "خطأ، هناك بيانات غير مكتملة";
    } else {
        $sql = "UPDATE accounts SET phone=? WHERE id=?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #067";
        } else {
            mysqli_stmt_bind_param($stmt, 'ss', $phone, $id);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #068";
            } else {
                $back['check'] = "success";
            }
        }
    }
    return $back;
}
function admin_orders($conn, $type)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM orders WHERE order_status=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #069";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $type);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #070";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] = $num;
            if ($num > 0) {
                $orders = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $orders[] = $row;
                }
                $back['orders'] = $orders;
            }
        }
    }
    return $back;
}
function admin_order_row($conn, $n, $order)
{
    $seller = is_user($conn, $order['seller']);
    $seller = $seller['user'];
    $buyer = is_user($conn, $order['buyer']);
    $buyer = $buyer['user'];
    ?>
    <tr>
        <td><?= $n ?></td>
        <td><a href="order?id=<?= $order['id'] ?>" class='text-dark font-weight-bold' target="_blank"><?= $order['title'] ?> (#<?= $order['id'] ?>)</a></td>
        <td><?= $order['id'] ?></td>
        <td><?= $order['cat_name'] ?></td>
        <td><?= short_name($seller) ?></td>
        <td><?= short_name($buyer) ?></td>
        <td><?= $order['price'] ?></td>
        <td><?= arabicDate($order['order_end']) ?></td>
    </tr>
<?php
}
function short_name($user)
{
    if ($user['verified'] == "yes") {
        $icon = "<i class='fas fa-check-circle ml-2 text-success ml-1 mr-1'></i>";
    } else {
        $icon = '';
    }
    $first_name = $user['first_name'];
    $last_name = explode(' ', $user['last_name']);
    $last_name = $last_name[0];
    $name = $icon . ' ' . $first_name . ' ' . $last_name;
    return $name;
}
function short_name_white($user)
{
    if ($user['verified'] == "yes") {
        $icon = "<i class='fas fa-check-circle ml-2 text-light ml-1 mr-1'></i>";
    } else {
        $icon = '';
    }
    $first_name = $user['first_name'];
    $last_name = explode(' ', $user['last_name']);
    $last_name = $last_name[0];
    $name = $icon . ' ' . $first_name . ' ' . $last_name;
    return $name;
}
function action_btn($order_id, $type)
{
    $type = strtolower($type);
    switch ($type) {
        case 'accept':
            $btn = "<button type='button' order='" . $order_id . "' class='btn ml-2 mb-2 btn-sm btn-success' id='acceptOrder'><i class='fas fa-check ml-1'></i>قبول تسليم الطلب</button>";
            break;
        case 'refuse':
            $btn = "<button type='button' order='" . $order_id . "' class='btn ml-2 mb-2 btn-sm btn-danger' id='refuseOrder'><i class='fas fa-ban ml-1'></i>رفض تسليم الطلب</button>";
            break;
        case 'submit':
            $btn = "<button type='button' order='" . $order_id . "' class='btn ml-2 mb-2 btn-sm btn-info grad-btn' id='submitOrder'>تسليم الطلب</button>";
            break;
        case 'cancel':
            $btn = "<button type='button' order='" . $order_id . "' class='btn ml-2 mb-2 btn-sm btn-danger' id='cancelOrder'><i class='fas fa-times ml-1'></i>إلغاء الطلب</button>";
            break;
    }
    return $btn;
}
function warranty_order($conn, $order, $seller, $value, $warranty)
{
    $back = array("error" => false);
    if (!$order || !$seller || !$warranty) {
        $back['error'] = true;
        $back['message'] = "خطأ، يجب إدخال كافة البيانات";
    } else {
        $start = time();
        $end = $start + ($warranty * 86400);
        $sql = "INSERT INTO warranty(order_id, order_seller, warranty_value, warranty_start, warranty_end) VALUES(?,?,?,?,?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #069";
        } else {
            mysqli_stmt_bind_param($stmt, 'sssss', $order, $seller, $value, $start, $end);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #070";
            } else {
                $back['check'] = "success";
            }
        }
    }
    return $back;
}
function order_warranty($conn, $order)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM warranty WHERE order_id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #071";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $order);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #072";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $check = mysqli_num_rows($result);
            if ($check == 0) {
                $back['warranty'] = false;
            } else {
                $back['warranty'] = true;
            }
        }
    }
    return $back;
}
function tax($conn)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM setting";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #073";
    } else {
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #074";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $back['tax'] = $row['tax'];
        }
    }
    return $back;
}
function update_tax($conn, $tax)
{
    $back = array("error" => false);
    if (!$tax) {
        $back['error'] = true;
        $back['message'] = "يجب إدخال قيمة العمولة";
    } else {
        if ($tax < 100 && $tax > 0) {
            $sql = "UPDATE setting SET tax=?";
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #075";
            } else {
                mysqli_stmt_bind_param($stmt, 's', $tax);
                if (!mysqli_stmt_execute($stmt)) {
                    $back['error'] = true;
                    $back['message'] = "خطأ تقني #076";
                } else {
                    $back['message'] = "تم تحديث العمولة وسيتم تطبيقها على كل الطلبات الجديدة التي سيتم إجرائها بداية من هذه اللحظة";
                }
            }
        } else {
            $back['error'] = true;
            $back['message'] = "يجب أن تكون قيمة العمولة أكثر من (0) و أقل من (100)";
        }
    }
    return $back;
}
function visible_report_status($status)
{
    switch ($status) {
        case 'running':
            $back = "جارية";
            break;
        case 'closed':
            $back = "مغلقة";
            break;
    }
    return $back;
}
function my_reports($conn)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM reports WHERE user=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #077";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $_SESSION['id']);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #078";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] = $num;
            if ($num > 0) {
                $reports = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $reports[] = $row;
                }
                $back['reports'] = $reports;
            }
        }
    }
    return $back;
}
function report_item($n, $report_item)
{
?>
    <tr>
        <td><?= $n; ?></td>
        <td><a href="report?id=<?= $report_item['id'] ?>" class="font-weight-bold text-dark"><?= $report_item['title'] ?></a></td>
        <td>#<?= $report_item['id'] ?></td>
        <td><?= arabicDate($report_item['report_timestamp']) ?></td>
        <td><?= visible_report_status($report_item['report_status']) ?></td>
    </tr>
<?php
}
function new_inbox_message($conn, $name, $email, $phone, $title, $message)
{
    $back = array("error" => false);
    if (!$name || !$email || !$phone || !$message) {
        $back['error'] = true;
        $back['message'] = "يجب إدخال كافة البيانات";
    } else {
        $sql = "INSERT INTO inbox (name, email, phone, title, message, message_timestamp) VALUES(?,?,?,?,?,?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #079 <br> ";
        } else {
            $now = time();
            mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $phone, $title, $message, $now);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #080";
            } else {
                $back['message'] = "تم إرسال رسالتك بنجاح، نعدك بأننا سنقوم بمراجعتها والتواصل معك في اقرب وقت ممكن";
                $admins = admins($conn);
                $admins = $admins['admins'];
                foreach ($admins as $admin) {
                    send_noti($conn, $admin, 'يوجد رسالة جديدة من أحد الزوار إضغط للإنتقال لصفحة الرسائل', 'inbox');
                }
            }
        }
    }
    return $back;
}
function admin_reports($conn)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM reports";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #081";
    } else {
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #082";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] = $num;
            if ($num > 0) {
                $reports = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $reports[] = $row;
                }
                $back['reports'] = $reports;
            }
        }
    }
    return $back;
}
function report($conn, $id)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM reports WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #083";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $id);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #084";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] = $num;
            if ($num > 0) {
                $row = mysqli_fetch_assoc($result);
                $back['report'] = $row;
                $user = is_user($conn, $row['user']);
                $name = short_name($user['user']);
                $back['name'] = $name;
            }
        }
    }
    return $back;
}
function admin_report_row($n, $conn, $report)
{
    $user = is_user($conn, $report['user']);
    $name = short_name($user['user']);
?>
    <tr>
        <td><?= $n; ?></td>
        <td><a href="report?id=<?= $report['id'] ?>" class="font-weight-bold text-dark"><?= $report['title'] ?></a></td>
        <td>#<?= $report['id'] ?></td>
        <td><?= $name ?></td>
        <td><?= arabicDate($report['report_timestamp']) ?></td>
        <td><?= visible_report_status($report['report_status']) ?></td>
    </tr>
<?php
}
function inbox($conn)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM inbox ORDER BY message_timestamp DESC"; 
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #085";
    } else {
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #086";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] = $num;
            if ($num > 0) {
                $messages = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $messages[] = $row;
                }
                $back['messages'] = $messages;
            }
        }
    }
    return $back;
}
function inbox_row($n, $message)
{
?>
    <tr>
        <td><?= $n ?></td>
        <td><a href="#" class="view-message text-dark font-weight-bold" title="<?= $message['title'] ?>" sender="<?= $message['name'] ?>" message="<?= nl2br($message['message']) ?>" date="<?= arabicDate($message['message_timestamp']) . ' ' . timeHour($message['message_timestamp']) ?>" email="<?= $message['email'] ?>" phone="<?= $message['phone'] ?>"><?= $message['title'] ?></a>
        </td>
        <td><?= $message['name'] ?></td>
        <td><?= arabicDate($message['message_timestamp']) ?></td>
    </tr>
<?php
}
function new_receipt($file)
{
    $back = array("error" => false);
    $now = time();
    $name = $file['name'];
    $type = $file['type'];
    $tmp = $file['tmp_name'];
    $file_error = $file['error'];
    $size = $file['size'];
    $ext = explode('.', $name);
    $ext = strtolower(end($ext));
    if ($file_error == 4) {
        $back['error'] = true;
        $back['message'] = "يجب إختيار ملف أولاً";
    } else {
        $allowed_ext = array('png', 'jpg', 'jpeg', 'xlsx', 'pdf', 'docx', 'txt', 'rtf', 'rar', 'zip');
        if (!in_array($ext, $allowed_ext)) {
            $back['error'] = true;
            $back['message'] = "صيغة الملف غير مسموح بها";
        } else {
            $new_name = "Wastetco-receipt-" . random_int(10000, 99999) . ".jpg";
            $path = '../attachments/receipts/' . $new_name;
            if ($size > 10000000) {
                $back['error'] = true;
                $back['message'] = "حجم الملف اكبر من المسموح به";
            } else {
                while (file_exists($path)) {
                    $new_name = "Wastetco-receipt-" . random_int(10000, 99999) . ".jpg";
                    $path = '../attachments/receipts/' . $new_name;
                }
                if (!move_uploaded_file($tmp, $path)) {
                    $back['error'] = true;
                    $back['message'] = "لم نستطع تحميل صورة البطاقة الخاصة بك، يرجى المحاولة مجدداً";
                } else {
                    $back['name'] = $new_name;
                }
            }
        }
    }
    return $back;
}
function visible_message_type($type)
{
    switch ($type) {
        case 'order':
            $data = "الطلب";
            break;
        case 'report':
            $data = "الشكوى";
            break;
    }
    return $data;
}
function admins($conn)
{
    $back = array("error" => false);
    $sql = "SELECT id FROM accounts WHERE acc_type=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #087";
    } else {
        $type = "admin";
        mysqli_stmt_bind_param($stmt, 's', $type);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #088";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] = $num;
            if ($num > 0) {
                $admins = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $admins[] = $row['id'];
                }
                $back['admins'] = $admins;
            }
        }
    }
    return $back;
}
function visible_report_action($action)
{
    switch ($action) {
        case 'running':
            $data = "فتح";
            break;
        case 'closed':
            $data = "غلق";
            break;
    }
    return $data;
}
function verify_action($conn, $user, $action)
{
    $back = array("error" => false);
    $sql = "UPDATE accounts SET verified=? WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #089";
    } else {
        mysqli_stmt_bind_param($stmt, 'ss', $action, $user);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #090";
        } else {
            switch ($action) {
                case 'yes':
                    $title = "تهانينا!! لقد حصلت على شارة التوثيق، مع تمنياتنا لك بالتوفيق ودوام عملك الرائع";
                    $message = "تم توثيق الحساب";
                    break;
                case 'no':
                    $title = "أسف لهذا، لقد تم نزع شارة التوثيق منك، إن كان هناك اي استفسار عن سبب حدوث ذلك تفضل بإنشاء شكوى لنتمكن من مراجعة الأمر";
                    $message = "تم إلغاء توثيق الحساب";
                    break;
            }
            $back['message'] = $message;
            send_noti($conn, $user, $title, '#');
        }
    }
    return $back;
}
function visit_noti($conn)
{
    $back = array("error" => false);
    $sql = "UPDATE accounts SET noti_visit=? WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #091";
    } else {
        $now = time();
        mysqli_stmt_bind_param($stmt, 'ss', $now, $_SESSION['id']);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #092";
        }
    }
}
function check_noti($conn)
{
    $back = array("error" => false);
    $user = is_user($conn, $_SESSION['id']);
    if ($user['error']) {
        $back['error'] = true;
        $back['message'] = $user['message'];
    } else {
        $last_visit = $user['user']['noti_visit'];
        $sql = "SELECT id FROM noti WHERE noti_to=? AND noti_timestamp >= ? LIMIT 10";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #093";
        } else {
            mysqli_stmt_bind_param($stmt, 's', $_SESSION['id'], $last_visit);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #094";
            } else {
                $result = mysqli_stmt_get_result($stmt);
                $num = mysqli_num_rows($result);
                if ($num > 9) {
                    $num = "+9";
                }
                $back['num'] = $num;
            }
        }
    }
    return $back;
}
function notifications($conn)
{
    $back = array("error" => false);
    $sql = "SELECT * FROM noti WHERE noti_to=? ORDER BY id DESC LIMIT 100";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #093";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $_SESSION['id']);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #094";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] = $num;
            if ($num > 0) {
                $notis = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $notis[] = $row;
                }
                $back['notifications'] = $notis;
            }
        }
    }
    return $back;
}
function notification_row($notification)
{
?>
    <div class="notifications-item">
        <div class="container">
            <div class="row">
                <div class="col-1 text-center">
                    <i class="fas fa-bell fa-lg"></i>
                </div>
                <div class="col-11">
                    <a href="<?= $notification['noti_url'] ?>" class="font-weight-bold"><?= $notification['noti_title'] ?></a>
                    <span class="notification-details d-block mt-2">
                        <i class="fas fa-calendar-alt ml-1"></i>
                        التاريخ:
                        <?= arabicDate($notification['noti_timestamp']) ?>
                        <i class="fas fa-clock ml-1"></i>
                        الوقت:
                        <?= timeHour($notification['noti_timestamp']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
<?php
}
function remember()
{
    if (!empty($_COOKIE['id']) && empty($_SESSION['id'])) {
        $_SESSION['id'] = $_COOKIE['id'];
        $_SESSION['first_name'] = $_COOKIE['first_name'];
        $_SESSION['last_name'] = $_COOKIE['last_name'];
        $_SESSION['email'] = $_COOKIE['email'];
        $_SESSION['phone'] = $_COOKIE['phone'];
        $_SESSION['nid'] = $_COOKIE['nid'];
        $_SESSION['account_status'] = $_COOKIE['account_status'];
        $_SESSION['acc_type'] = $_COOKIE['acc_type'];
        $_SESSION['verified'] = $_COOKIE['verified'];
        // لا تعين $_SESSION['admin'] = 1 هنا، اعتمد على acc_type
    }
}
function is_admin($conn, $user)
{
    $back = array('error' => false);
    $user_data = is_user($conn, $user); 
    if ($user_data['error']) {
        $back = $user_data;
    } else {
        $user_data = $user_data['user'];
        if ($user_data['acc_type'] == "admin") {
            $back['check'] =  true;
        } else {
            $back['check'] =  false;
        }
    }
    return $back;
}
function update_pwd($conn, $user, $pwd)
{
    $back = array('error' => false);
    if (!$user || $user < 1) {
        $back['error'] = true;
        $back['message'] = "هذا الحساب غير موجود";
    } else {
        if (!$pwd) {
            $back['error'] = true;
            $back['message'] = "يجب إدخال كلمة السر";
        } else {
            $pwd = password_hash($pwd, PASSWORD_DEFAULT);
            if ($user == $_SESSION['id'] || $_SESSION['acc_type'] == "admin") {
                $sql = "UPDATE accounts SET pwd=? WHERE id=?";
                $stmt = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($stmt, $sql)) {
                    $back['error'] = true;
                    $back['message'] = "خطأ تقني #095";
                } else {
                    mysqli_stmt_bind_param($stmt, 'ss', $pwd, $user);
                    if (!mysqli_stmt_execute($stmt)) {
                        $back['error'] = true;
                        $back['message'] = "خطأ تقني #096";
                    } else {
                        $back['message'] = "تم تحديث كلمة السر بنجاح";
                    }
                }
            } else {
                $back['error'] = true;
                $back['message'] = "لا تملك الصلاحية لتغير كلمة السر لهذا الحساب";
            }
        }
    }
    return $back;
}
function update_email($conn, $user, $email)
{
    $back = array('error' => false);
    if (!$user || $user < 1) {
        $back['error'] = true;
        $back['message'] = "هذا الحساب غير موجود";
    } else {
        $email = safe($email, 'mail');
        if (!$email) {
            $back['error'] = true;
            $back['message'] = "يجب إدخال بريد إلكتروني صحيح";
        } else {
            if ($user == $_SESSION['id'] || $_SESSION['acc_type'] == "admin") {
                $sql = "UPDATE accounts SET email=? WHERE id=?";
                $stmt = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($stmt, $sql)) {
                    $back['error'] = true;
                    $back['message'] = "خطأ تقني #095";
                } else {
                    mysqli_stmt_bind_param($stmt, 'ss', $email, $user);
                    if (!mysqli_stmt_execute($stmt)) {
                        $back['error'] = true;
                        $back['message'] = "خطأ تقني #096";
                    } else {
                        $back['message'] = "تم تحديث كلمة السر بنجاح";
                    }
                }
            } else {
                $back['error'] = true;
                $back['message'] = "لا تملك الصلاحية لتغير كلمة السر لهذا الحساب";
            }
        }
    }
    return $back;
}
function delete_order($conn, $id)
{
    $back = array('error' => false);
    $sql = "DELETE FROM orders WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #097";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $id);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #098";
        }
    }
    return $back;
}
function new_cat($conn, $cat) {
    $back = array("error"=>false);
    if(empty($cat)){
        $back['error'] = true;
        $back['message'] = "يجب إدخال إسم التصنيف المرد إنشائه";
    }else{
        $sql = "INSERT INTO cats (name) VALUES(?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #099";
        } else {
            mysqli_stmt_bind_param($stmt, 's', $cat);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #100";
            }else{
                $back['message'] = "تم إنشاء التصنيف بنجاح";
            }
        }
    }
    return $back;
}
function delete_cat($conn, $cat){
    $back = array("error"=>false);
    if(!$cat){
        $back['error'] = true;
        $back['message'] = "خطاّ!! يرجى إعادة تحميل الصفحة والمحاولة مجدداً";
    }else{
        $sql = "DELETE FROM cats WHERE id=?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #101";
        } else {
            mysqli_stmt_bind_param($stmt, 's', $cat);
            if (!mysqli_stmt_execute($stmt)) {
                $back['error'] = true;
                $back['message'] = "خطأ تقني #102";
            }else{
                $back['message'] = "تم حذف التصنيف بنجاح";
            }
        }
    }
    return $back;
}
function cats($conn){
    $back = array("error"=>false);
    $sql = "SELECT * FROM cats";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #103";
    } else {
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #104";
        }else{
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] =$num;
            if($num > 0){
                $cats = array();
                while($row = mysqli_fetch_assoc($result)){
                    $cats[] = $row;
                }
                $back['cats'] = $cats;
            }
        }
    }
    return $back;
}
function cat($conn, $cat){
    $back = array("error"=>false);
    $sql = "SELECT * FROM cats WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #105";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $cat);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #106";
        }else{
            $result = mysqli_stmt_get_result($stmt);
            $num = mysqli_num_rows($result);
            $back['num'] =$num;
            if($num > 0){
                $cat = mysqli_fetch_assoc($result);
                $back['cat'] = $cat;
            }
        }
    }
    return $back;
}
function remove_row($conn, $table, $id){
    $back = array("error"=>false);
    $sql = "DELETE FROM $table WHERE id=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $back['error'] = true;
        $back['message'] = "خطأ تقني #107";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $id);
        if (!mysqli_stmt_execute($stmt)) {
            $back['error'] = true;
            $back['message'] = "خطأ تقني #108";
        }
    }
    return $back;
}