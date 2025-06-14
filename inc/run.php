<?php
session_start();
require 'func.php'; // تأكد أن المسار صحيح لملف func.php

$target = safe($_POST['target'], 'string');
switch ($target) {
    case 'signup':
        $back = array("error" => false);
        $first_name = safe($_POST['firstname'], 'string');
        $last_name = safe($_POST['lastname'], 'string');
        $email = safe($_POST['email'], 'mail');
        $phone = safe($_POST['phone'], 'string');
        $pwd1 = safe($_POST['pwd1'], 'string');
        $pwd2 = safe($_POST['pwd2'], 'string');
        $check_email = check_email($conn, $email);

        if ($check_email['check'] || empty($email)) {
            $back['error'] = true;
            $back['message'] = "هذا البريد الإلكتروني مستخدم بالفعل، يرجى إستخدام بريد إلكتروني أخر";
        } else {
            if ($pwd1 != $pwd2) {
                $back['error'] = true;
                $back['message'] = "كلمتين السر غير متطابقتين يرجى إعادة إدخالهم ثم المحاولة مجدداً";
            } else {
                $pwd = $pwd1;
                if (strlen($pwd) < 8) {
                    $back['error'] = true;
                    $back['message'] = "لا يمكن ان تكون كلمة السر اقل من 8 أرقام / حروف";
                } else {
                    $signup = signup($conn, $first_name, $last_name, $email, $pwd, $phone); // تمرير phone هنا
                    if ($signup['error']) {
                        $back['error'] = true;
                        $back['message'] = $signup['message'];
                    } else {
                        // يجب أن تكون الدالة id_card_upload موجودة في func.php وتستقبل $conn
                        // وتحفظ الصورة في المسار الصحيح مثل attachments/id_cards/
                        // وإذا كان ملف signup.php يرسل 'idcard' في $_FILES
                        $file = $_FILES['idcard'];
                        $id_card = id_card_upload($signup['id'], $file); // تمرير $signup['id'] فقط لـ id_card_upload
                        if ($id_card['error']) {
                            $back['error'] = true;
                            $back['message'] = $id_card['message'];
                            remove_row($conn, 'accounts', $signup['id']); // حذف الحساب إذا فشل رفع البطاقة
                            remove_row($conn, 'user_locations', $signup['id']); // حذف سجل الموقع إذا فشل رفع البطاقة
                        } else {
                            $back['message'] = "تم إنشاء الحساب بنجاح وهو قيد المراجعة الأن، عند إنتهاء عملية المراجعة ستستلم بريد إلكتروني يفيد بقبول حسابك او رفضه";
                        }
                    }
                }
            }
        }
        echo json_encode($back);
        break;
    case 'login':
        $back = array('error' => false);
        $email = safe($_POST['email'], 'mail');
        $pwd = safe($_POST['pwd'], 'string');
        $remember = safe($_POST['remember'], 'string');
        if (empty($email) || empty($pwd)) {
            $back['error'] = true;
            $back['message'] = "يجب إدخال كافة البيانات";
        } else {
            $login = login($conn, $email, $pwd, $remember);
            if ($login['error']) {
                $back['error'] = true;
                $back['message'] = $login['message'];
            } else {
                if ($_SESSION['account_status'] == "Waiting") {
                    $back['error'] = true;
                    $back['message'] = "مازال حسابك قيد المراجعة، سيتم الإنتهاء منها خلال 24 ساعه وعندها ستستلم بريد إلكتروني يفيد بتفعيل حسابك، في حالة قابلتك أي مشاكل يمكنك التواصل معنا من خلال <a href='index#contactUs'>الضغط هنا</a>";
                } else {
                    if ($_SESSION['account_status'] == "Banned") {
                        $back['error'] = true;
                        $back['message'] = "هذا الحساب محظور في حالة وجود مشكلة يمكنك التواصل معنا من خلال <a href='index#contactUs'>الضغط هنا</a>";
                    } else {
                        $back['url'] = $login['url'];
                    }
                }
            }
        }
        echo json_encode($back);
        break;
    case 'logout':
        session_unset();
        session_destroy();
        // إزالة الكوكيز بشكل صحيح
        setcookie('id', '', time() - 3600, '/', 'wastetco.com');
        setcookie('first_name', '', time() - 3600, '/', 'wastetco.com');
        setcookie('last_name', '', time() - 3600, '/', 'wastetco.com');
        setcookie('email', '', time() - 3600, '/', 'wastetco.com');
        setcookie('phone', '', time() - 3600, '/', 'wastetco.com');
        setcookie('nid', '', time() - 3600, '/', 'wastetco.com');
        setcookie('account_status', '', time() - 3600, '/', 'wastetco.com');
        setcookie('acc_type', '', time() - 3600, '/', 'wastetco.com');
        setcookie('verified', '', time() - 3600, '/', 'wastetco.com');
        setcookie('admin', '', time() - 3600, '/', 'wastetco.com');
        // إزالة كوكيز الموقع المضافة بواسطة JS
        setcookie('location_attempted', '', time() - 3600, '/', 'wastetco.com');
        setcookie('user_latitude', '', time() - 3600, '/', 'wastetco.com');
        setcookie('user_longitude', '', time() - 3600, '/', 'wastetco.com');
        setcookie('location_source', '', time() - 3600, '/', 'wastetco.com');
        break;
    case 'get-profile-data':
        // هذا الـ case في run.php (في pages/account.php) يستدعيه الجافاسكريبت
        // لتعبئة بيانات البروفايل، يجب التأكد من أنه يرسل معلومات المستخدم بشكل صحيح
        $back['verified'] = $_SESSION['verified'];
        $back['firstname'] = $_SESSION['first_name'];
        $back['lastname'] = $_SESSION['last_name'];
        $back['email'] = $_SESSION['email'];
        $back['nid'] = $_SESSION['nid'];
        $back['phone'] = $_SESSION['phone'];
        echo json_encode($back);
        break;
    case 'update-phone':
        $phone = safe($_POST['phone'], 'string');
        echo json_encode(update_phone($conn, $phone));
        break;
    case 'admin-update-phone':
        $phone = safe($_POST['phone'], 'string');
        $id = safe($_POST['id'], 'int');
        echo json_encode(admin_update_phone($conn, $id, $phone)); // يجب استدعاء admin_update_phone وليس update_phone
        break;
    case 'add-request':
        $code = safe($_POST['code'], 'string');
        $add = add_request($conn, $code);
        if ($add['error']) {
            $back['error'] = true;
            $back['message'] = $add['message'];
        } else {
            $back['message'] = "تم إرسال طلب إضافة الرصيد بنجاح سيتم مراجعة الطلب خلال 12 ساعة وسيصلك إشعار وبريد إلكتروني يعلمك بقرار المراجعة بمجرد الإنتهاء";
        }
        echo json_encode($back);
        break;
    case 'withdraw-request':
        $value = safe($_POST['value'], 'string');
        $balance = balance($conn, $_SESSION['id']);
        $available = $balance['available'];
        if ($value > $available) {
            $back['error'] = true;
            $back['message'] = 'لا يمكنك سحب رصيد أكثر من المتاح في خانة "الرصيد المتاح"';
        } else {
            $withdraw = withdraw_request($conn, $value);
            if ($withdraw['error']) {
                $back['error'] = true;
                $back['message'] = $withdraw['message'];
            } else {
                $back['available'] = $withdraw['available'];
                $back['banned'] = $withdraw['banned'];
                $back['message'] = "تم إرسال طلب سحب الرصيد الخاص بك بنجاح سيتم تحويل الرصيد إليك في خلال مدة أقصاها 6 ساعات كما ستتلقى إشعار بمجرد تحويل المبلغ المطلوب";
            }
        }
        echo json_encode($back);
        break;
    case 'new-message':
        $mess = prepare_string(safe($_POST['new_message'], 'string'));
        $to_type = safe($_POST['type'], 'string');
        $to_id = safe($_POST['for_id'], 'int');
        // $new_message = new_message($conn, $mess, $to_type, $to_id);
        echo json_encode(new_message($conn, $mess, $to_type, $to_id));
        break;
    case 'new-report':
        $user = $_SESSION['id'];
        $title = safe($_POST['title'], 'string');
        $description = prepare_string($_POST['description']);
        echo json_encode(new_report($conn, $user, $title, $description));
        break;
    case 'action-report':
        $report = safe($_POST['report'], 'int');
        $action = safe($_POST['action'], 'string');
        echo json_encode(action_report($conn, $report, $action));
        break;
    case 'check-user':
        $user_id = safe($_POST['seller'], 'int');
        echo json_encode(is_user($conn, $user_id));
        break;
    case 'new-order':
        $back = array("error" => false);
        $title = safe($_POST['title'], 'string');
        $seller = safe($_POST['seller'], 'int');
        $deadline = safe($_POST['deadline'], 'int');
        $warranty = safe($_POST['warranty'], 'int');
        $price = safe($_POST['price'], 'string');
        $cat = safe($_POST['cat'], 'int');
        $description = prepare_string($_POST['description']);
        $buyer = $_SESSION['id'];
        if (!$title || !$seller || !$deadline || !$price || !$description) {
            $back['error'] = true;
            $back['message'] = "يجب ملئ كافة الحقول";
        } else {
            $check_seller = is_user($conn, $seller);
            if ($check_seller['error']) {
                $back['error'] = true;
                $back['message'] = $check_seller['message'];
            } else {
                if ($seller == $buyer) {
                    $back['error'] = true;
                    $back['message'] = "لا يمكنك إرسال طلب إلى نفسك";
                } else {
                    $back = new_order($conn, $title, $cat, $description, $buyer, $seller, $price, $deadline, $warranty);
                }
            }
        }
        echo json_encode($back);
        break;
    case 'upload-order-attachment':
        $for_id = safe($_POST['order_id'], 'int');
        $file = $_FILES['new-attachment'];
        echo json_encode(new_attachment($conn, $for_id, $file));
        break;
    case 'active-ban-account':
        $id = safe($_POST['id'], 'int');
        $status = safe($_POST['status'], 'string');
        echo json_encode(active_ban_account($conn, $id, $status));
        break;
    case 'active-account':
        $nid = safe($_POST['nid'], 'int');
        $id = safe($_POST['id'], 'int');
        echo json_encode(active_account($conn, $id, $nid));
        break;
    case 'accept-add-request':
        $val = safe($_POST['val'], 'int');
        $request = safe($_POST['request'], 'int');
        echo json_encode(approve_add_request($conn, $request, $val));
        break;
    case 'refuse-add-request':
        $request = safe($_POST['request'], 'int');
        echo json_encode(refuse_add_request($conn, $request));
        break;
    case 'accept-withdraw-request':
        $request = safe($_POST['request'], 'int');
        echo json_encode(approve_withdraw_request($conn, $request));
        break;
    case 'my-purches':
        echo json_encode(orders($conn, $_SESSION['id'], 'purches', ''));
        break;
    case 'submit-order':
        $order_id = safe($_POST['order_id'], 'int');
        echo json_encode(submit_order($conn, $order_id));
        break;
    case 'accept-order':
        $order_id = safe($_POST['order_id'], 'int');
        echo json_encode(accept_order($conn, $order_id));
        break;
    case 'cancel-order':
        $order_id = safe($_POST['order_id'], 'int');
        echo json_encode(cancel_order($conn, $order_id));
        break;
    case 'refuse-order':
        $order_id = safe($_POST['order_id'], 'int');
        $reason = prepare_string($_POST['reason']);
        echo json_encode(refuse_order($conn, $order_id, $reason));
        break;
    case 'update-tax':
        echo json_encode(update_tax($conn, safe($_POST['new_tax'], 'string')));
        break;
    case 'new-inbox-message':
        $name = safe($_POST['name'], 'string');
        $email = safe($_POST['email'], 'string');
        $phone = safe($_POST['phone'], 'string');
        $title = safe($_POST['title'], 'string');
        $message = safe($_POST['message'], 'string');
        echo json_encode(new_inbox_message($conn, $name, $email, $phone, $title, $message));
        break;
    case 'action-verify':
        $id = safe($_POST['id'], 'int');
        $action = safe($_POST['action'], 'string');
        echo json_encode(verify_action($conn, $id, $action));
        break;
    case 'check-noti':
        echo json_encode(check_noti($conn));
        break;
    case 'update-pwd':
        $pwd = $_POST['pwd'];
        $user = safe($_POST['user'], 'int');
        echo json_encode(update_pwd($conn, $user, $pwd));
        break;
    case 'update-email':
        $email = $_POST['email'];
        $user = safe($_POST['user'], 'int');
        echo json_encode(update_email($conn, $user, $email));
        break;
    case 'new-cat':
        $cat = safe($_POST['cat'], 'string');
        echo json_encode(new_cat($conn, $cat));
        break;
    case 'delete-cat':
        $cat = safe($_POST['cat'], 'int');
        echo json_encode(delete_cat($conn, $cat));
        break;
    // إضافة حالة جديدة لمعالجة تحديث الموقع الدقيق من JavaScript
    case 'update_precise_location':
        $latitude = safe($_POST['latitude'], 'string'); // قد تكون float
        $longitude = safe($_POST['longitude'], 'string'); // قد تكون float
        $user_id = $_SESSION['id'] ?? null; // ID المستخدم الحالي

        $back = ['error' => false, 'message' => ''];

        if ($user_id) {
            $user_ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            // استدعاء دالة تحديث الموقع، مع تمرير الموقع الدقيق من المتصفح
            update_user_location($conn, $user_id, $user_ip, $user_agent, (float)$latitude, (float)$longitude);
            $back['message'] = "تم تحديث الموقع الدقيق بنجاح.";
        } else {
            $back['error'] = true;
            $back['message'] = "المستخدم غير مسجل الدخول، لا يمكن تحديث الموقع.";
        }
        echo json_encode($back);
        break;
    default:
        # code...
        break;
}

?>